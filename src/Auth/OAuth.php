<?php

namespace Nomba\Auth;

use GuzzleHttp\Client;
use Nomba\Exceptions\AuthenticationException;

class OAuth
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private ?string $token = null;
    private int $expiresAt = 0;

    public function __construct(string $clientId, string $clientSecret, string $baseUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get the access token, fetching a new one if expired.
     *
     * @return string
     * @throws AuthenticationException
     */
    public function getAccessToken(): string
    {
        if ($this->token && time() < $this->expiresAt) {
            return $this->token;
        }

        return $this->authenticate();
    }

    /**
     * Authenticate with Nomba and fetch a new token.
     *
     * @return string
     * @throws AuthenticationException
     */
    public function authenticate(): string
    {
        $client = new Client();

        try {
            $response = $client->post($this->baseUrl . '/v1/auth/token', [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'])) {
                throw new AuthenticationException("Authentication failed: Access token not found in response.");
            }

            $this->token = $data['access_token'];
            // Set expiry time (defaulting to 1 hour if not provided)
            $expiresIn = $data['expires_in'] ?? 3600;
            $this->expiresAt = time() + ($expiresIn - 60); // Subtract 60 seconds for safety

            return $this->token;
        } catch (\Exception $e) {
            throw new AuthenticationException("Authentication failed: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}

