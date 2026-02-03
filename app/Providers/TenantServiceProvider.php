<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar TenantManager como singleton
        $this->app->singleton('tenant-manager', function ($app) {
            return new TenantManager();
        });
    }

    public function boot(): void
    {
        // Log cuando se establece un tenant
        Log::info('TenantServiceProvider iniciado');
    }
} 