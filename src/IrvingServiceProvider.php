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

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        // $this->publishConfig();
    }

    /**
     * Publish Irving Config.
     */
    protected function publishConfig(): void
    {
        /* $this->publishes( [
            realpath(__DIR__ . '/./config/irving.php') => base_path('config/irving.php'),
        ] );

        $this->mergeConfigFrom(
            realpath(__DIR__ . '/./config/irving.php'),
            'irving'
        ); */
    }
}
