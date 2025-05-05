<?php

namespace App\Providers;

use App\Services\ServiceRole;
use Illuminate\Support\ServiceProvider;

// Fournisseur de service pour le service de rôles
class FournisseurServiceRole extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ServiceRole::class, fn() => new ServiceRole());
    }
}