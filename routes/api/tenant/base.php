<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api',
    InitializeTenancyByPath::class,
    PreventAccessFromCentralDomains::class,
])
     ->prefix('/{tenant}')
     ->group(function () {
         require base_path('routes/api/tenant/cases.php');

         Route::get('/', function () {

//             $centralUsers = tenancy()->central(function ($tenant) {
//                 return \App\Models\Tentant\User::all();
//             });
//
//             dd($centralUsers->toArray());
//
//             dd(\App\Models\Tentant\User::all()->toArray());

//             return \App\Models\Tenant\UseCase::whereId('1')->with(['user'])->first();
//             return  \App\Models\User::whereId('9c7ff1c8-8713-4c55-bc83-feb13a2fa59d')->with(['useCases'])->first();

             return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
         });
     });
