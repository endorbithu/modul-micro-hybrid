<?php

declare(strict_types=1);

namespace EndorbitHu\ModuleMicroHybrid\Providers;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
  
    public function boot()
    { 
        if (!$this->app->routesAreCached()) {
            include __DIR__ . '/../../routes/api.php';      
        }   
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/module-micro-hybrid.php' => config_path('module-micro-hybrid.php'),
            ]);
        }
    }
}

