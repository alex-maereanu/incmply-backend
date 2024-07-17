<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Token;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::loadKeysFrom(base_path('storage'));
        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
    }
}
