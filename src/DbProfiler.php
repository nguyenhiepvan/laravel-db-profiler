<?php
/**
 * Created by PhpStorm.
 * User: Hiệp Nguyễn
 * Date: 19/08/2021
 * Time: 18:16
 */


namespace Illuminated\Database;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class DbProfiler
{
    /**
     * The query counter.
     *
     * @var int
     */
    private $counter = 1;

    /**
     * The Laravel application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var array
     */
    private static $queries = [];


    /**
     * @param Application|null $app
     */
    public function __construct(Application $app = null)
    {
        if (!$app) {
            $app = app();   //Fallback when $app is not given
        }
        $this->app = $app;
    }

    /**
     * Boot the service provider.
     *
     * @return void
     *
     */
    public function boot(): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        DB::listen(function (QueryExecuted $query) {
            $i    = $this->counter++;
            $sql  = $this->applyQueryBindings($query->sql, $query->bindings);
            $time = $query->time;
            if ($this->app->runningInConsole()) {
                dump("[{$i}]: {$sql}; ({$time} ms)");
            } else {
                self::$queries[] = "<!-- [{$i}]: {$sql}; ({$time} ms)-->";
            }
        });
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        if (!$this->app->isLocal() && !config('db-profiler.force')) {
            return false;
        }
        return $this->app->runningInConsole()
            ? collect($_SERVER['argv'])->contains('-vvv')
            : (Request::exists('vvv') || strpos(Request::header("referer", ""), "vvv") !== false);
    }


    /**
     * @param Response|JsonResponse $response
     * @return JsonResponse|Response
     */
    public function modifyResponse($response)
    {
        if ($response instanceof JsonResponse) {
            $contents                               = json_decode($response->getContent(), true);
            $contents[config("db-profiler.append")] = self::$queries;
            $contents                               = json_encode($contents);
        } else {
            $contents = str_replace("</body>",
                implode("\n", self::$queries) . "</body>",
                $response->getContent());
        }
        return $response->setContent($contents);
    }

    /**
     * Apply query bindings to the given SQL query.
     *
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    private function applyQueryBindings(string $sql, array $bindings): string
    {
        $bindings = collect($bindings)->map(function ($binding) {
            switch (gettype($binding)) {
                case 'boolean':
                    return (int)$binding;
                case 'string':
                    return "'{$binding}'";
                default:
                    return $binding;
            }
        })->toArray();

        return Str::replaceArray('?', $bindings, $sql);
    }

}
