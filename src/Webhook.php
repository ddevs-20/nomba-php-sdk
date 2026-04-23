<?php

namespace Nomba;

class Webhook
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * Verify a webhook signature.
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verify(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, $this->secret);
        return hash_equals($expected, $signature);
    }
}

