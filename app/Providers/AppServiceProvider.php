<?php

namespace App\Providers;

use App\Models\Shop;
use App\Models\User;
use App\Policies\ShopPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

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
        Cashier::useCustomerModel(User::class);
        Cashier::calculateTaxes();

        // Registrar policy manualmente
        Gate::policy(Shop::class, ShopPolicy::class);

        // Opcional: Registrar un listener para las consultas SQL para depuración
        /*DB::listen(function ($query) {
            Log::info('Consulta SQL ejecutada:', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });*/
    }
}
