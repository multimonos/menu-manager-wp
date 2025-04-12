<?php

namespace MenuManager\Vendor\Illuminate\Events;

use MenuManager\Vendor\Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use MenuManager\Vendor\Illuminate\Support\ServiceProvider;
class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('events', function ($app) {
            return (new \MenuManager\Vendor\Illuminate\Events\Dispatcher($app))->setQueueResolver(function () use($app) {
                return $app->make(QueueFactoryContract::class);
            })->setTransactionManagerResolver(function () use($app) {
                return $app->bound('db.transactions') ? $app->make('db.transactions') : null;
            });
        });
    }
}
