<?php
namespace Nomba\Webhook;

class Verifier {
    public static function verify($payload,$signature,$secret){
        return hash_equals(hash_hmac('sha256',$payload,$secret),$signature);
    }
}
