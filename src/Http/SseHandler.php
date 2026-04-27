<?php

namespace Nomba\Http;

use Nomba\NombaClient;

class SseHandler
{
    private NombaClient $client;

    public function __construct(NombaClient $client)
    {
        $this->client = $client;
    }

    /**
     * Start the SSE stream for a specific order reference.
     *
     * @param string $orderRef
     * @param int $interval Seconds between polls
     * @param int $timeout Maximum time to poll in seconds
     */
    public function streamStatus(string $orderRef, int $interval = 3, int $timeout = 600): void
    {
        // Disable output buffering
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_flush();
        }
        ob_implicit_flush(true);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // For Nginx

        $startTime = time();

        while (true) {
            if (time() - $startTime > $timeout) {
                $this->sendEvent(['status' => 'TIMEOUT', 'message' => 'Polling timed out']);
                break;
            }

            try {
                // We use checkouts to fetch transaction status by orderRef
                // Based on Nomba docs, you might need to fetch by orderRef or transactionRef
                $transaction = $this->client->payments->fetchTransaction($orderRef);
                $status = $transaction['data']['status'] ?? 'PENDING';

                $this->sendEvent([
                    'status' => $status,
                    'orderRef' => $orderRef,
                    'message' => $transaction['data']['message'] ?? ''
                ]);

                if (in_array($status, ['SUCCESS', 'FAILED', 'CANCELLED', 'EXPIRED'])) {
                    break;
                }
            } catch (\Exception $e) {
                $this->sendEvent([
                    'status' => 'ERROR',
                    'message' => $e->getMessage()
                ]);
            }

            if (connection_aborted()) break;

            sleep($interval);
        }
    }

    private function sendEvent(array $data): void
    {
        echo "data: " . json_encode($data) . "\n\n";
        flush();
    }
}

