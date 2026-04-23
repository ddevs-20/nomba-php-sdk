<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class DirectDebits
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a direct debit mandate.
     *
     * @param array $data
     * @return array
     */
    public function createMandate(array $data): array
    {
        return $this->http->request('POST', 'v1/direct-debit/mandate', ['json' => $data]);
    }
}

