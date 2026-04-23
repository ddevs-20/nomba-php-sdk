<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class Transfers
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Fetch bank codes and names.
     *
     * @return array
     */
    public function fetchBanks(): array
    {
        return $this->http->request('GET', 'v1/transfers/banks');
    }

    /**
     * Perform bank account lookup.
     *
     * @param array $data
     * @return array
     */
    public function lookup(array $data): array
    {
        return $this->http->request('POST', 'v1/transfers/bank/lookup', ['json' => $data]);
    }

    /**
     * Perform bank account transfer from the parent account.
     *
     * @param array $data
     * @return array
     */
    public function initiate(array $data): array
    {
        return $this->http->request('POST', 'v1/transfers/bank/parent', ['json' => $data]);
    }

    /**
     * Get transfer status.
     *
     * @param string $transactionRef
     * @return array
     */
    public function getStatus(string $transactionRef): array
    {
        return $this->http->request('GET', "v1/transfers/{$transactionRef}");
    }

    /**
     * Perform wallet transfer from the parent account.
     *
     * @param array $data
     * @return array
     */
    public function walletTransfer(array $data): array
    {
        return $this->http->request('POST', 'v1/transfers/wallet/parent', ['json' => $data]);
    }
}

