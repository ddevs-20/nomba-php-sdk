<?php
namespace Nomba\StateMachine;

class TransferStateMachine {
    const PENDING='pending';
    const PROCESSING='processing';
    const SUCCESS='success';
    const FAILED='failed';

    public function transition($from,$to){
        $valid=[
            self::PENDING=>[self::PROCESSING,self::FAILED],
            self::PROCESSING=>[self::SUCCESS,self::FAILED]
        ];
        if(!in_array($to,$valid[$from]??[])){
            throw new \Exception("Invalid transition");
        }
        return $to;
    }
}
