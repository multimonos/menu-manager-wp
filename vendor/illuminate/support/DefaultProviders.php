<?php

namespace MenuManager\Vendor\Illuminate\Support;

class DefaultProviders
{
    /**
     * The current providers.
     *
     * @var array
     */
    protected $providers;
    /**
     * Create a new default provider collection.
     *
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?: [\MenuManager\Vendor\Illuminate\Auth\AuthServiceProvider::class, \MenuManager\Vendor\Illuminate\Broadcasting\BroadcastServiceProvider::class, \MenuManager\Vendor\Illuminate\Bus\BusServiceProvider::class, \MenuManager\Vendor\Illuminate\Cache\CacheServiceProvider::class, \MenuManager\Vendor\Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class, \MenuManager\Vendor\Illuminate\Cookie\CookieServiceProvider::class, \MenuManager\Vendor\Illuminate\Database\DatabaseServiceProvider::class, \MenuManager\Vendor\Illuminate\Encryption\EncryptionServiceProvider::class, \MenuManager\Vendor\Illuminate\Filesystem\FilesystemServiceProvider::class, \MenuManager\Vendor\Illuminate\Foundation\Providers\FoundationServiceProvider::class, \MenuManager\Vendor\Illuminate\Hashing\HashServiceProvider::class, \MenuManager\Vendor\Illuminate\Mail\MailServiceProvider::class, \MenuManager\Vendor\Illuminate\Notifications\NotificationServiceProvider::class, \MenuManager\Vendor\Illuminate\Pagination\PaginationServiceProvider::class, \MenuManager\Vendor\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class, \MenuManager\Vendor\Illuminate\Pipeline\PipelineServiceProvider::class, \MenuManager\Vendor\Illuminate\Queue\QueueServiceProvider::class, \MenuManager\Vendor\Illuminate\Redis\RedisServiceProvider::class, \MenuManager\Vendor\Illuminate\Session\SessionServiceProvider::class, \MenuManager\Vendor\Illuminate\Translation\TranslationServiceProvider::class, \MenuManager\Vendor\Illuminate\Validation\ValidationServiceProvider::class, \MenuManager\Vendor\Illuminate\View\ViewServiceProvider::class];
    }
    /**
     * Merge the given providers into the provider collection.
     *
     * @param  array  $providers
     * @return static
     */
    public function merge(array $providers)
    {
        $this->providers = \array_merge($this->providers, $providers);
        return new static($this->providers);
    }
    /**
     * Replace the given providers with other providers.
     *
     * @param  array  $items
     * @return static
     */
    public function replace(array $replacements)
    {
        $current = collect($this->providers);
        foreach ($replacements as $from => $to) {
            $key = $current->search($from);
            $current = \is_int($key) ? $current->replace([$key => $to]) : $current;
        }
        return new static($current->values()->toArray());
    }
    /**
     * Disable the given providers.
     *
     * @param  array  $providers
     * @return static
     */
    public function except(array $providers)
    {
        return new static(collect($this->providers)->reject(fn($p) => \in_array($p, $providers))->values()->toArray());
    }
    /**
     * Convert the provider collection to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->providers;
    }
}
