<?php 

namespace Unlu\Laravel\Api;

use Illuminate\Support\ServiceProvider;

class ApiQueryBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('api-query-builder.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config.php', 'api-query-builder'
        );
    }
}