<?php

namespace MenuManager\Vendor\Illuminate\Pipeline;

use MenuManager\Vendor\Illuminate\Contracts\Pipeline\Hub as PipelineHubContract;
use MenuManager\Vendor\Illuminate\Contracts\Support\DeferrableProvider;
use MenuManager\Vendor\Illuminate\Support\ServiceProvider;
class PipelineServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PipelineHubContract::class, \MenuManager\Vendor\Illuminate\Pipeline\Hub::class);
        $this->app->bind('pipeline', fn($app) => new \MenuManager\Vendor\Illuminate\Pipeline\Pipeline($app));
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [PipelineHubContract::class, 'pipeline'];
    }
}
