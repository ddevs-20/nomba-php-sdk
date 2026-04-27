(function (window) {
    'use strict';

    const NombaCheckout = {
        init: function (config) {
            this.config = config;
            this.injectStyles();
            return this;
        },

        injectStyles: function () {
            if (document.getElementById('nomba-checkout-styles')) return;

            const style = document.createElement('style');
            style.id = 'nomba-checkout-styles';
            style.textContent = `
                :root {
                    --nomba-overlay: rgba(0, 0, 0, 0.6);
                    --nomba-white: #ffffff;
                }

                .nomba-modal-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: var(--nomba-overlay);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 99999;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                    backdrop-filter: blur(8px);
                }

                .nomba-modal-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }

                .nomba-modal-container {
                    background: var(--nomba-white);
                    width: 95%;
                    max-width: 500px;
                    height: 90vh;
                    max-height: 700px;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                    transform: scale(0.9);
                    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
                    position: relative;
                }

                .nomba-modal-overlay.active .nomba-modal-container {
                    transform: scale(1);
                }

                .nomba-iframe {
                    width: 100%;
                    height: 100%;
                    border: none;
                    background: white;
                }

                .nomba-close-trigger {
                    position: absolute;
                    top: -40px;
                    right: 0;
                    color: white;
                    font-size: 30px;
                    cursor: pointer;
                    font-family: Arial, sans-serif;
                    line-height: 1;
                }

                .nomba-loader-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: white;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    z-index: 10;
                    transition: opacity 0.5s ease;
                }

                .nomba-spinner {
                    width: 50px;
                    height: 50px;
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid #f8c705;
                    border-radius: 50%;
                    animation: nomba-spin 1s linear infinite;
                    margin-bottom: 20px;
                }

                @keyframes nomba-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                @media (max-width: 600px) {
                    .nomba-modal-container {
                        width: 100%;
                        height: 100vh;
                        max-height: none;
                        border-radius: 0;
                    }
                    .nomba-close-trigger {
                        top: 20px;
                        right: 20px;
                        color: #333;
                    }
                }
            `;
            document.head.appendChild(style);
        },

        open: function () {
            this.createModal();
            setTimeout(() => {
                document.querySelector('.nomba-modal-overlay').classList.add('active');
            }, 50);
            this.startStatusPolling();
        },

        close: function () {
            const overlay = document.querySelector('.nomba-modal-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                setTimeout(() => {
                    if (overlay.parentNode) {
                        document.body.removeChild(overlay);
                    }
                    if (this.eventSource) {
                        this.eventSource.close();
                        this.eventSource = null;
                    }
                }, 300);
            }
        },

        createModal: function () {
            const overlay = document.createElement('div');
            overlay.className = 'nomba-modal-overlay';
            
            overlay.innerHTML = `
                <div class="nomba-modal-container">
                    <div class="nomba-close-trigger">&times;</div>
                    <div class="nomba-loader-overlay" id="nomba-loader">
                        <div class="nomba-spinner"></div>
                        <p style="font-family: sans-serif; color: #666; font-size: 14px;">Securely connecting to Nomba...</p>
                    </div>
                    <iframe 
                        src="${this.config.checkoutUrl}" 
                        class="nomba-iframe" 
                        id="nomba-iframe"
                        onload="document.getElementById('nomba-loader').style.opacity = '0'; setTimeout(() => document.getElementById('nomba-loader').style.display = 'none', 500);"
                    ></iframe>
                </div>
            `;

            document.body.appendChild(overlay);

            overlay.querySelector('.nomba-close-trigger').onclick = () => {
                if (confirm("Are you sure you want to cancel this payment?")) {
                    this.close();
                    if (this.config.onClose) this.config.onClose();
                }
            };
        },

        startStatusPolling: function () {
            if (!this.config.sseUrl || !this.config.orderRef) return;

            const url = new URL(this.config.sseUrl, window.location.href);
            url.searchParams.append('orderRef', this.config.orderRef);

            this.eventSource = new EventSource(url.toString());
            
            this.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    
                    if (data.status === 'SUCCESS') {
                        if (this.eventSource) {
                            this.eventSource.close();
                            this.eventSource = null;
                        }
                        
                        if (this.config.onSuccess) {
                            this.config.onSuccess(data);
                        } else if (this.config.redirectUrl) {
                            window.location.href = this.config.redirectUrl;
                        } else {
                            alert("Payment Successful!");
                            this.close();
                        }
                    } else if (data.status === 'FAILED' || data.status === 'CANCELLED') {
                        if (this.eventSource) {
                            this.eventSource.close();
                            this.eventSource = null;
                        }
                        
                        if (this.config.onError) {
                            this.config.onError(data);
                        } else {
                            alert("Payment " + data.status.toLowerCase());
                            this.close();
                        }
                    }
                } catch (e) {
                    console.error("Error parsing SSE data", e);
                }
            };

            this.eventSource.onerror = (e) => {
                console.error("SSE connection connection failed. Check your sseUrl.", e);
            };
        }
    };

    window.NombaCheckout = NombaCheckout;

})(window);
