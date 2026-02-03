<?php


namespace App\Tenancy;

use App\Models\Tenant;

class TenantManager
{
    protected static $currentTenant;

    public static function setCurrentTenant(?Tenant $tenant): void
    {
        
        self::$currentTenant = $tenant;
    }

    public static function getCurrentTenant(): ?Tenant
    {
    
        return self::$currentTenant;
    }

    public static function getCurrentTenantId(): ?int
    {
        return self::$currentTenant ? self::$currentTenant->id : null;
    }

    public static function checkCurrent(): bool
    {
        return !is_null(self::$currentTenant);
    }
}