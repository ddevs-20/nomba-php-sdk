<?php

namespace Nomba;

use Nomba\Auth\OAuth;
use Nomba\Http\HttpClient;
use Nomba\Resources\Accounts;
use Nomba\Resources\VirtualAccounts;
use Nomba\Resources\Payments;
use Nomba\Resources\Transfers;
use Nomba\Resources\Webhooks;
use Nomba\Resources\Terminals;
use Nomba\Resources\DirectDebits;

/**
 * @property Accounts $accounts
 * @property VirtualAccounts $virtualAccounts
 * @property Payments $payments
 * @property Transfers $transfers
 * @property Webhooks $webhooks
 * @property Terminals $terminals
 * @property DirectDebits $directDebit
 */
class NombaClient
{
    private string $baseUrl;
    private HttpClient $http;
    private OAuth $auth;
    private array $resources = [];

    private const BASE_URL_PRODUCTION = 'https://api.nomba.com';
    private const BASE_URL_SANDBOX = 'https://sandbox.nomba.com';

    public function __construct(array $config)
    {
        $clientId = $config['client_id'] ?? '';
        $clientSecret = $config['client_secret'] ?? '';
        $accountId = $config['account_id'] ?? '';
        $environment = $config['environment'] ?? 'production';

        $this->baseUrl = $config['base_url'] ?? ($environment === 'sandbox' ? self::BASE_URL_SANDBOX : self::BASE_URL_PRODUCTION);

        $this->auth = new OAuth($clientId, $clientSecret, $this->baseUrl);
        $this->http = new HttpClient($this->baseUrl, $this->auth, $accountId, [
            'timeout' => $config['timeout'] ?? 30.0,
            'max_retries' => $config['max_retries'] ?? 3,
        ]);
    }

    /**
     * Authenticate and pre-fetch access token.
     *
     * @return string
     * @throws Exceptions\AuthenticationException
     */
    public function authenticate(): string
    {
        return $this->auth->authenticate();
    }

    /**
     * Magic getter for resources.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        $resourceMap = [
            'accounts' => Accounts::class,
            'virtualAccounts' => VirtualAccounts::class,
            'payments' => Payments::class,
            'transfers' => Transfers::class,
            'webhooks' => Webhooks::class,
            'terminals' => Terminals::class,
            'directDebit' => DirectDebits::class,
        ];

        if (isset($resourceMap[$name])) {
            if (!isset($this->resources[$name])) {
                $resourceClass = $resourceMap[$name];
                $this->resources[$name] = new $resourceClass($this->http);
            }
            return $this->resources[$name];
        }

        trigger_error('Undefined property via __get(): ' . $name, E_USER_NOTICE);
        return null;
    }
}
