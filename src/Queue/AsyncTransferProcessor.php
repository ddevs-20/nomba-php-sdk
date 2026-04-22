<?php
namespace Nomba\Queue;

class AsyncTransferProcessor {
    public function process($transfers,$client){
        foreach($transfers as $t){
            $client->http->request('POST','/v1/transfers',['json'=>$t]);
        }
    }
}
