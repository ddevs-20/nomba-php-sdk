<?php

namespace Nomba\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Nomba\Auth\OAuth;
use Nomba\Exceptions\NombaApiException;
use Nomba\Exceptions\ValidationException;
use Nomba\Exceptions\AuthenticationException;

class HttpClient
{
    private Client $client;
    private OAuth $auth;
    private string $accountId;

    public function __construct(string $baseUrl, OAuth $auth, string $accountId, array $options = [])
    {
        $this->auth = $auth;
        $this->accountId = $accountId;

        $stack = HandlerStack::create();
        
        // Add retry middleware
        $stack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
            if ($retries >= ($options['max_retries'] ?? 3)) {
                return false;
            }
            if ($exception instanceof \GuzzleHttp\Exception\ConnectException) {
                return true;
            }
            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }
            return false;
        }));

        $this->client = new Client([
            'base_uri' => rtrim($baseUrl, '/') . '/',
            'handler' => $stack,
            'timeout' => $options['timeout'] ?? 30.0,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'accountId' => $this->accountId,
            ]
        ]);
    }

    /**
     * Send a request to the Nomba API.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws NombaApiException
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        try {
            $options['headers']['Authorization'] = 'Bearer ' . $this->auth->getAccessToken();

            $response = $this->client->request($method, ltrim($uri, '/'), $options);
            $body = json_decode($response->getBody(), true);

            return $body;
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (\Exception $e) {
            throw new NombaApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Handle Guzzle request exceptions.
     *
     * @param RequestException $e
     * @throws NombaApiException
     */
    private function handleRequestException(RequestException $e)
    {
        $response = $e->getResponse();
        $statusCode = $response ? $response->getStatusCode() : 500;
        $responseBody = $response ? json_decode($response->getBody(), true) : [];

        $message = $responseBody['message'] ?? $e->getMessage();

        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message, $statusCode, $responseBody);
            case 422:
                throw new ValidationException($message, $statusCode, $responseBody, $responseBody['errors'] ?? []);
            default:
                throw new NombaApiException($message, $statusCode, $responseBody);
        }
    }
}


