<?php
namespace Nomba\Auth;
use GuzzleHttp\Client;

class OAuth {
    private $id,$secret,$url,$token,$exp;

    public function __construct($id,$secret,$url){
        $this->id=$id;$this->secret=$secret;$this->url=$url;
    }

    public function getAccessToken(){
        if($this->token && time() < $this->exp) return $this->token;

        $c=new Client();
        $r=$c->post($this->url.'/oauth/token',[
            'form_params'=>[
                'grant_type'=>'client_credentials',
                'client_id'=>$this->id,
                'client_secret'=>$this->secret
            ]
        ]);

        $d=json_decode($r->getBody(),true);
        $this->token=$d['access_token'];
        $this->exp=time()+3600;

        return $this->token;
    }
}
