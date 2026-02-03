<?php

namespace App\Providers;

use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class TenantStorageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // exteder a cada tenant dinamicamente
        Storage::extend('tenant_public', function ($app, $config) {
            $tenantSlug = $this->getTenantSlug();
            
            $config['root'] = storage_path('app/public/tenants/' . $tenantSlug);
            $config['url'] = env('APP_URL') . '/storage/tenants/' . $tenantSlug;
            
            return Storage::createLocalDriver($config);
        });
    }
    
    private function getTenantSlug()
    {
        $currentTenant = TenantManager::getCurrentTenant();
        
        if ($currentTenant && !empty($currentTenant->slug)) {
            return $currentTenant->slug;
        }
        return '';
    }

    public function register()
    {
        //
    }
}