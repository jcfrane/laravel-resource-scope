<?php

namespace JCFrane\ResourceScope;

use Illuminate\Support\ServiceProvider;
use JCFrane\ResourceScope\Middleware\SetResourceScope;
use JCFrane\ResourceScope\Support\ScopeResolver;

class ResourceScopeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/resource-scope.php', 'resource-scope');

        $this->app->singleton(ResourceScopeManager::class);
        $this->app->singleton(ScopeResolver::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/resource-scope.php' => config_path('resource-scope.php'),
            ], 'resource-scope-config');
        }

        $this->app['router']->aliasMiddleware('resource.scope', SetResourceScope::class);
    }
}
