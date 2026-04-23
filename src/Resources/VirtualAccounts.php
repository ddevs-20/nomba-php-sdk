<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class VirtualAccounts
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a virtual account.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        return $this->http->request('POST', 'v1/accounts/virtual', ['json' => $data]);
    }

    /**
     * Create a virtual account for a sub-account.
     *
     * @param array $data
          * @return array
     */
    public function createForSubAccount(array $data): array
    {
        return $this->http->request('POST', 'v1/accounts/virtual/sub', ['json' => $data]);
    }

    /**
     * Filter virtual accounts.
     *
     * @param array $data
     * @return array
     */
    public function filter(array $data): array
    {
        return $this->http->request('POST', 'v1/accounts/virtual/filter', ['json' => $data]);
    }

    /**
     * Fetch a virtual account.
     *
     * @param string $accountNumber
     * @return array
     */
    public function fetch(string $accountNumber): array
    {
        return $this->http->request('GET', "v1/accounts/virtual/{$accountNumber}");
}

    /**
     * Update a virtual account.
     *
     * @param string $accountNumber
          * @param array $data
     * @return array
     */
    public function update(string $accountNumber, array $data): array
    {
        return $this->http->request('PUT', "v1/accounts/virtual/{$accountNumber}", ['json' => $data]);
    }

    /**
     * Expire a virtual account.
     *
     * @param string $accountNumber
     * @return array
          */
    public function expire(string $accountNumber): array
    {
        return $this->http->request('DELETE', "v1/accounts/virtual/{$accountNumber}");
    }
}

