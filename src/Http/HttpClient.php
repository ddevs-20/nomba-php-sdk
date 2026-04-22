<?php
namespace Nomba\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class HttpClient {
    private $client,$auth;

    public function __construct($base,$auth){
        $this->auth=$auth;

        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(function ($retries,$request,$response,$exception) {
            if ($retries >= 3) return false;
            if ($exception) return true;
            if ($response && $response->getStatusCode() >= 500) return true;
            return false;
        }));

        $this->client = new Client([
            'base_uri'=>$base,
            'handler'=>$stack
        ]);
    }

    public function request($method,$uri,$opts=[]){
        $opts['headers']['Authorization']='Bearer '.$this->auth->getAccessToken();
        $opts['headers']['Accept']='application/json';
        return json_decode($this->client->request($method,$uri,$opts)->getBody(),true);
    }
}
