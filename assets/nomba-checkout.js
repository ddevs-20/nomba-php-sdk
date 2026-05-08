(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.NombaCheckout = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    const NombaCheckout = {
        // ... rest of the code ...
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
            this.statusShown = false;
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
                        <p style="font-family: sans-serif; color: #666; font-size: 14px;" id="nomba-loader-text">Securely connecting to Nomba...</p>
                    </div>
                    <iframe 
                        src="${this.config.checkoutUrl}" 
                        class="nomba-iframe" 
                        id="nomba-iframe"
                        onload="if(!NombaCheckout.statusShown) { document.getElementById('nomba-loader').style.opacity = '0'; setTimeout(() => { if(!NombaCheckout.statusShown) document.getElementById('nomba-loader').style.display = 'none' }, 500); }"
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

        updateStatusUI: function (status, data) {
            const loader = document.getElementById('nomba-loader');
            if (!loader) return;

            this.statusShown = true;
            loader.style.display = 'flex';
            loader.style.opacity = '1';
            loader.style.background = 'rgba(255, 255, 255, 0.98)';
            
            const statusMap = {
                'SUCCESS': { title: 'Payment Successful', icon: '✓', color: '#22c55e', btn: true, msg: 'Your transaction was completed successfully.' },
                'FAILED': { title: 'Payment Failed', icon: '✕', color: '#ef4444', btn: true, msg: 'There was an issue processing your payment.' },
                'CANCELLED': { title: 'Payment Cancelled', icon: '✕', color: '#64748b', btn: true, msg: 'The transaction was cancelled.' },
                'EXPIRED': { title: 'Payment Expired', icon: '!', color: '#f59e0b', btn: true, msg: 'The payment session has expired.' },
                'ERROR': { title: 'Processing Error', icon: '!', color: '#ef4444', btn: true, msg: 'An error occurred during processing.' },
                'PENDING': { title: 'Processing Payment...', icon: '<div class="nomba-spinner" style="margin: 0 auto;"></div>', color: '#f8c705', btn: false, msg: 'Please wait while we confirm your payment.' },
                'PROCESSING': { title: 'Confirming Transaction...', icon: '<div class="nomba-spinner" style="margin: 0 auto;"></div>', color: '#f8c705', btn: false, msg: 'We are verifying your transaction with the bank.' }
            };

            const config = statusMap[status] || { title: 'Payment ' + status, icon: '?', color: '#64748b', btn: true, msg: data.message || '' };

            loader.innerHTML = `
                <div style="text-align: center; padding: 30px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; width: 100%; box-sizing: border-box;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: ${['PENDING', 'PROCESSING'].includes(status) ? 'transparent' : config.color}; color: white; display: flex; align-items: center; justify-content: center; font-size: 35px; margin: 0 auto 25px; transition: all 0.3s ease; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                        ${config.icon}
                    </div>
                    <h2 style="margin: 0 0 10px; color: #1a1a1a; font-size: 22px; font-weight: 600; letter-spacing: -0.025em;">${config.title}</h2>
                    <p style="margin: 0 0 25px; color: #666; font-size: 15px; line-height: 1.5; max-width: 280px; margin-left: auto; margin-right: auto;">${data.message || config.msg}</p>
                    ${config.btn ? `<button id="nomba-status-close" style="background: #f8c705; border: none; padding: 12px 35px; border-radius: 8px; cursor: pointer; font-weight: 600; color: #1a1a1a; font-size: 15px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">Close Checkout</button>` : ''}
                </div>
            `;

            if (config.btn) {
                const btn = document.getElementById('nomba-status-close');
                if (btn) {
                    btn.onclick = () => this.close();
                    btn.onmouseover = () => btn.style.transform = 'scale(1.02)';
                    btn.onmouseout = () => btn.style.transform = 'scale(1)';
                }
            }
        },

        startStatusPolling: function () {
            if (!this.config.sseUrl || !this.config.orderRef) return;

            const url = new URL(this.config.sseUrl, window.location.href);
            url.searchParams.append('orderRef', this.config.orderRef);

            this.eventSource = new EventSource(url.toString());
            
            this.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    
                    // Show status in UI
                    this.updateStatusUI(data.status, data);

                    const terminalStatuses = ['SUCCESS', 'FAILED', 'CANCELLED', 'EXPIRED', 'ERROR'];
                    
                    if (terminalStatuses.includes(data.status)) {
                        if (this.eventSource) {
                            this.eventSource.close();
                            this.eventSource = null;
                        }
                        
                        // If user provided callbacks or redirect, execute them after a delay
                        // so they can see the final status in the modal
                        if (data.status === 'SUCCESS') {
                            if (this.config.onSuccess) {
                                setTimeout(() => this.config.onSuccess(data), 2000);
                            } else if (this.config.redirectUrl) {
                                setTimeout(() => window.location.href = this.config.redirectUrl, 2000);
                            }
                        } else {
                            if (this.config.onError) {
                                setTimeout(() => this.config.onError(data), 2000);
                            }
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

    return NombaCheckout;
}));

