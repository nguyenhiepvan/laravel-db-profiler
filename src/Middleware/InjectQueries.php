<?php
/**
 * Created by PhpStorm.
 * User: Hiệp Nguyễn
 * Date: 19/08/2021
 * Time: 17:54
 */

namespace Illuminated\Database\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminated\Database\DbProfiler;

class InjectQueries
{
    /**
     * The DbProfiler instance
     *
     * @var DbProfiler
     */
    protected $profiler;

    /**
     * Create a new middleware instance.
     *
     * @param DbProfiler $profiler
     */
    public function __construct(DbProfiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->profiler->isEnabled()) {
            return $next($request);
        }

        $this->profiler->boot();

        $response = $next($request);

        return $this->profiler->modifyResponse($response);
    }
}

