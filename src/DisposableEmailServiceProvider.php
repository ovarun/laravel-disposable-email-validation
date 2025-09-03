<?php

namespace Ovarun\DisposableEmail;

use Illuminate\Support\ServiceProvider;

class DisposableEmailServiceProvider extends ServiceProvider
{
    public function boot()
    {        
    if ($this->app->runningInConsole()) {
        $this->commands([
            \Ovarun\DisposableEmail\Console\UpdateBlocklist::class,
        ]);
    }

    $this->publishes([
        __DIR__ . '/../config/disposable-email.php' => config_path('disposable-email.php'),
    ], 'config');

    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/disposable-email.php',
            'disposable-email'
        );
    }
}
