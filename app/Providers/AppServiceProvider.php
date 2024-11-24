<?php

declare(strict_types=1);

namespace App\Providers;

use Laravel\Lumen\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void // нет гарантии что остальные поставщики загружены
    {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void // тут всегда будут доступны другие поставщики услуг
    {
        Auth::provider('users-service', function (Application $app, array $config) {
            return new ApiGetwayUserProvider();
        });
    }
}
