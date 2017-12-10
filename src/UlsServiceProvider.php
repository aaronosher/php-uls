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
    }
}
