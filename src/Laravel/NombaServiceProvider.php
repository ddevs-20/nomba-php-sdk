<?php
namespace Nomba\Laravel;

use Illuminate\Support\ServiceProvider;
use Nomba\NombaClient;

class NombaServiceProvider extends ServiceProvider {
    public function register(){
        $this->app->singleton(NombaClient::class,function(){
            return new NombaClient(
                config('nomba.client_id'),
                config('nomba.client_secret')
            );
        });
    }
}
