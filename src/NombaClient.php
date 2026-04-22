<?php
namespace Nomba;

use Nomba\Auth\OAuth;
use Nomba\Http\HttpClient;

class NombaClient {
    public $http;
    public function __construct($id,$secret,$base='https://api.nomba.com'){
        $auth = new OAuth($id,$secret,$base);
        $this->http = new HttpClient($base,$auth);
    }
}
