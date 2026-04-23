<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use App\Models\Genre;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
                $genres = cache()->remember('movie_genres', 86400, function () {
                    return [
                        ['id' => 28, 'name' => 'Action'],
                        ['id' => 878, 'name' => 'Science Fiction'],
                    ];
                });

                $view->with('genres', $genres);
        });
    }
}
