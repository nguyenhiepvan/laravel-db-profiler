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
     *
     * @noinspection ForgottenDebugOutputInspection
     */
    public function boot()
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
    protected function getConfigPath()
    {
        return config_path('db-profiler.php');
    }

    /**
     * Register the Inject Queries Middleware
     *
     * @param string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }
}
