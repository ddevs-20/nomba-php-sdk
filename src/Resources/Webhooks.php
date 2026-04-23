<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class Webhooks
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Re-push a webhook for a transaction.
     *
     * @param array $data
     * @return array
     */
    public function rePush(array $data): array
    {
        return $this->http->request('POST', 'v1/webhooks/re-push', ['json' => $data]);
    }
}
