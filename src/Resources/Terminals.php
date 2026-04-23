<?php

namespace Nomba\Resources;

use Nomba\Http\HttpClient;

class Terminals
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    /**
     * Create a terminal action.
     *
     * @param array $data
     * @return array
     */
    public function createAction(array $data): array
    {
        return $this->http->request('POST', 'v1/terminals/actions', ['json' => $data]);
    }
}

