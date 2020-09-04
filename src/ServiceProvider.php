<?php

namespace Iben\Statable;

use Iben\Statable\Services\StateHistoryManager;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/state-machine.php' => config_path('state-machine.php'),
            ], 'config');
        }

        if (! class_exists('CreateStateHistoryTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_state_history_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_state_history_table.php'),
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('StateHistoryManager', function () {
            return new StateHistoryManager();
        });
    }
}
