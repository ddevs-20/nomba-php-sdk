<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class Accounts
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Fetch parent account details.
     *
     * @return array
     */
    public function fetchParentDetails(): array
    {
        return $this->http->request('GET', 'v1/accounts/parent');
    }

    /**
     * Fetch sub account details.
     *
     * @param string $subAccountId
     * @return array
     */
    public function fetchSubAccountDetails(string $subAccountId): array
    {
        return $this->http->request('GET', "v1/accounts/sub/{$subAccountId}");
    }

    /**
     * Fetch parent account balance.
     *
     * @return array
     */
    public function fetchParentBalance(): array
    {
        return $this->http->request('GET', 'v1/accounts/parent/balance');
    }

    /**
     * Fetch sub account balance.
     *
     * @param string $subAccountId
     * @return array
     */
    public function fetchSubAccountBalance(string $subAccountId): array
    {
        return $this->http->request('GET', "v1/accounts/sub/{$subAccountId}/balance");
    }

    /**
     * Fetch terminals assigned to the parent account.
     *
     * @return array
     */
    public function fetchParentTerminals(): array
    {
        return $this->http->request('GET', 'v1/accounts/parent/terminals');
    }

    /**
     * Fetch terminals assigned to a sub account.
     *
     * @param string $subAccountId
     * @return array
     */
    public function fetchSubAccountTerminals(string $subAccountId): array
    {
        return $this->http->request('GET', "v1/accounts/sub/{$subAccountId}/terminals");
    }
}
