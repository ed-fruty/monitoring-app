<?php

namespace App\Providers;

use App\Common\Loader\JsonLoader;
use App\Common\Loader\YamlLoader;
use App\Contracts\CheckRepository;
use App\Common\Loader\SyncFileLoader;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Config\FileLocator;
use App\Repositories\EloquentCheckRepository;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\LoaderResolver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(CheckRepository::class, EloquentCheckRepository::class);


        $this->app->singleton(FileLocatorInterface::class, FileLocator::class);

        $this->app->singleton(SyncFileLoader::class, function () {
            $resolver = new LoaderResolver([
                $this->app->make(JsonLoader::class),
                $this->app->make(YamlLoader::class),
            ]);

            return new SyncFileLoader($resolver);
        });
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            CheckRepository::class,
            SyncFileLoader::class,
            FileLocatorInterface::class,
        ];
    }
}
