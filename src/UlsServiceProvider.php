<?php

namespace VATUSA\Uls;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class UlsServiceProvider extends BaseServiceProvider
{
    public function boot() {
        $this->publishes([
            __DIR__.'../config/uls.php' => config_path('uls.php'),
        ]);
    }

    public function provides() {
        return [Uls::class];
    }

    public function register() {
        /*$this->app->singleton(Uls::class, function ($app) {
            return new Uls();
        });*/
    }

    public function configPath() {
        return __DIR__ . "../config/uls.php";
    }
}
