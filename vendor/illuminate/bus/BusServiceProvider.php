<?php

namespace MenuManager\Vendor\Illuminate\Bus;

use MenuManager\Vendor\Aws\DynamoDb\DynamoDbClient;
use MenuManager\Vendor\Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use MenuManager\Vendor\Illuminate\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use MenuManager\Vendor\Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use MenuManager\Vendor\Illuminate\Contracts\Support\DeferrableProvider;
use MenuManager\Vendor\Illuminate\Support\Arr;
use MenuManager\Vendor\Illuminate\Support\ServiceProvider;
class BusServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\MenuManager\Vendor\Illuminate\Bus\Dispatcher::class, function ($app) {
            return new \MenuManager\Vendor\Illuminate\Bus\Dispatcher($app, function ($connection = null) use($app) {
                return $app[QueueFactoryContract::class]->connection($connection);
            });
        });
        $this->registerBatchServices();
        $this->app->alias(\MenuManager\Vendor\Illuminate\Bus\Dispatcher::class, DispatcherContract::class);
        $this->app->alias(\MenuManager\Vendor\Illuminate\Bus\Dispatcher::class, QueueingDispatcherContract::class);
    }
    /**
     * Register the batch handling services.
     *
     * @return void
     */
    protected function registerBatchServices()
    {
        $this->app->singleton(\MenuManager\Vendor\Illuminate\Bus\BatchRepository::class, function ($app) {
            $driver = $app->config->get('queue.batching.driver', 'database');
            return $driver === 'dynamodb' ? $app->make(\MenuManager\Vendor\Illuminate\Bus\DynamoBatchRepository::class) : $app->make(\MenuManager\Vendor\Illuminate\Bus\DatabaseBatchRepository::class);
        });
        $this->app->singleton(\MenuManager\Vendor\Illuminate\Bus\DatabaseBatchRepository::class, function ($app) {
            return new \MenuManager\Vendor\Illuminate\Bus\DatabaseBatchRepository($app->make(\MenuManager\Vendor\Illuminate\Bus\BatchFactory::class), $app->make('db')->connection($app->config->get('queue.batching.database')), $app->config->get('queue.batching.table', 'job_batches'));
        });
        $this->app->singleton(\MenuManager\Vendor\Illuminate\Bus\DynamoBatchRepository::class, function ($app) {
            $config = $app->config->get('queue.batching');
            $dynamoConfig = ['region' => $config['region'], 'version' => 'latest', 'endpoint' => $config['endpoint'] ?? null];
            if (!empty($config['key']) && !empty($config['secret'])) {
                $dynamoConfig['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
            }
            return new \MenuManager\Vendor\Illuminate\Bus\DynamoBatchRepository($app->make(\MenuManager\Vendor\Illuminate\Bus\BatchFactory::class), new DynamoDbClient($dynamoConfig), $app->config->get('app.name'), $app->config->get('queue.batching.table', 'job_batches'), ttl: $app->config->get('queue.batching.ttl', null), ttlAttribute: $app->config->get('queue.batching.ttl_attribute', 'ttl'));
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [\MenuManager\Vendor\Illuminate\Bus\Dispatcher::class, DispatcherContract::class, QueueingDispatcherContract::class, \MenuManager\Vendor\Illuminate\Bus\BatchRepository::class, \MenuManager\Vendor\Illuminate\Bus\DatabaseBatchRepository::class];
    }
}
