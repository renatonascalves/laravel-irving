<?php

namespace Irving;

use Illuminate\Support\ServiceProvider;

class IrvingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                IrvingCommand::class,
            ]);
        }
    }
}
