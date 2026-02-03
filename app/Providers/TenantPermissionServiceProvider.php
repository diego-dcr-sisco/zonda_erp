<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TenantPermissionFilter;

class TenantPermissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('tenant.permission.filter', function ($app) {
            return new TenantPermissionFilter();
        });
    }

      public function boot()
    {
        // Cargar helpers
        require_once app_path('Helpers/tenant_permissions.php');
    }
}