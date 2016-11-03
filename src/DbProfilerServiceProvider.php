<?php

namespace Illuminated\Database;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DbProfilerServiceProvider extends ServiceProvider
{
    private static $counter = 1;

    public function boot()
    {
        if (!$this->isEnabled()) {
            return;
        }

        DB::listen(function (QueryExecuted $query) {
            $i = self::tickCounter();
            $sql = $this->getSqlWithAppliedBindings($query);
            $time = $query->time;
            dump("[$i]: {$sql}; ({$time} ms)");
        });
    }

    private function isEnabled()
    {
        if ($this->app->environment('production')) {
            return false;
        }

        return in_array('-vvv', $_SERVER['argv']) || request()->exists('vvv');
    }

    private static function tickCounter()
    {
        return self::$counter++;
    }

    private function getSqlWithAppliedBindings(QueryExecuted $query)
    {
        $sql = $query->sql;
        $bindings = $this->prepareBindings($query->bindings);
        return str_replace_array('?', $bindings, $sql);
    }

    private function prepareBindings(array $bindings)
    {
        return array_map(function ($item) {
            return is_numeric($item) ? $item : "'{$item}'";
        }, $bindings);
    }
}
