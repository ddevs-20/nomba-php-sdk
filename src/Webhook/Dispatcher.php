<?php
namespace Nomba\Webhook;

class Dispatcher {
    private $handlers = [];

    public function listen($event,$callback){
        $this->handlers[$event]=$callback;
    }

    public function dispatch($event,$payload){
        if(isset($this->handlers[$event])){
            call_user_func($this->handlers[$event],$payload);
        }
    }
}
