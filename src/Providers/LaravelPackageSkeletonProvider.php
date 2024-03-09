<?php

declare(strict_types=1);

namespace Danidoble\LaravelPackageSkeleton\Providers;

use Illuminate\Support\ServiceProvider;

final class LaravelPackageSkeletonProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/package.php', 'package');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'package');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }
}
