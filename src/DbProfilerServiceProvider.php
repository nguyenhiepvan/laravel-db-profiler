<?php

namespace Illuminated\Database;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminated\Database\Middleware\InjectQueries;

class DbProfilerServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $configPath = __DIR__ . '/../config/db-profiler.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');
        $this->registerMiddleware(InjectQueries::class);
        if ($this->app->runningInConsole()) {
           (new DbProfiler($this->app))->boot();
        }
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath(): string
    {
        return config_path('db-profiler.php');
    }

    /**
     * Register the Inject Queries Middleware
     *
     * @param string $middleware
     */
    protected function registerMiddleware(string $middleware): void
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }
}
