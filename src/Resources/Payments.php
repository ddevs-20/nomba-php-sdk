<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class Payments
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create an online checkout order.
     *
     * @param array $data
     * @return array
     */
    public function createOrder(array $data): array
    {
        return $this->http->request('POST', 'v1/checkout/order', ['json' => $data]);
    }

    /**
     * Charge a customer using tokenized card data.
     *
     * @param array $data
     * @return array
     */
    public function chargeCard(array $data): array
    {
        return $this->http->request('POST', 'v1/checkout/charge/tokenized', ['json' => $data]);
    }

    /**
     * List tokenized cards.
     *
     * @return array
     */
    public function listTokenizedCards(): array
    {
        return $this->http->request('GET', 'v1/checkout/tokenized-cards');
    }

    /**
     * Update tokenized card data.
     *
     * @param array $data
     * @return array
     */
    public function updateTokenizedCard(array $data): array
    {
        return $this->http->request('POST', 'v1/checkout/tokenized-cards/update', ['json' => $data]);
    }

    /**
     * Delete tokenized card data.
     *
     * @param string $tokenId
     * @return array
     */
    public function deleteTokenizedCard(string $tokenId): array
    {
        return $this->http->request('DELETE', "v1/checkout/tokenized-cards/{$tokenId}");
    }

    /**
     * Fetch checkout transaction details.
     *
     * @param string $transactionRef
     * @return array
     */
    public function fetchTransaction(string $transactionRef): array
    {
        return $this->http->request('GET', "v1/checkout/transaction/{$transactionRef}");
    }

    /**
     * Refund a checkout transaction.
     *
     * @param array $data
     * @return array
     */
    public function refund(array $data): array
    {
        return $this->http->request('POST', 'v1/checkout/refund', ['json' => $data]);
    }

    /**
     * Cancel a checkout order.
     *
     * @param array $data
     * @return array
     */
    public function cancelOrder(array $data): array
    {
        return $this->http->request('POST', 'v1/checkout/order/cancel', ['json' => $data]);
    }
}

