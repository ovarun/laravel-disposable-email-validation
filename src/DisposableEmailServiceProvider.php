<?php

namespace Ovarun\DisposableEmail;

use Illuminate\Support\ServiceProvider;

class DisposableEmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/disposable-email.php',
            'disposable-email'
        );

        $this->app->singleton(DisposableEmailValidator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\UpdateBlocklist::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/disposable-email.php' => config_path('disposable-email.php'),
            ], 'config');
        }
    }
}
