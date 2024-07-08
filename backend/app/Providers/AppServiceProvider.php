<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		Schema::defaultStringLength(191);
        error_reporting(E_ERROR);
        if (config('app.debug_sql')) {
            DB::listen(function ($query) {
                if (!is_dir(public_path('logs'))) {
                    mkdir(public_path('logs'), 0755);
                }
                $sql = $query->sql;
                $count = 1;
                foreach ($query->bindings as $v) {
                    $sql = preg_replace('/\?/', $v, $sql, 1);
                }
                File::append(
                    storage_path('/logs/query-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $sql . '' . PHP_EOL
                );
            });
        }
    }
}
