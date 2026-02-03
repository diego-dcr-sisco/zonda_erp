<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait TenantScoped
{
    /**
     * Boot the trait.
     */
    protected static function bootTenantScoped()
    {
        static::creating(function (Model $model) {
            $currentTenantId = TenantManager::getCurrentTenantId();
            
            if ($currentTenantId && empty($model->tenant_id)) {
                $model->tenant_id = $currentTenantId;
                Log::info("Auto-asignado tenant_id: {$currentTenantId} a " . get_class($model));
            }
        });

        // Aplicar scope global automáticamente
        static::addGlobalScope('tenant', function (Builder $builder) {
            $currentTenantId = TenantManager::getCurrentTenantId();
            
            if ($currentTenantId) {
                // Usar el nombre correcto de la tabla del modelo
                $table = $builder->getModel()->getTable();
                $builder->where("{$table}.tenant_id", $currentTenantId);
                Log::info("Aplicando scope tenant: {$currentTenantId} en tabla: {$table} para modelo: " . get_class($builder->getModel()));
            }
        });
    }

    /**
     * Scope para consultas sin filtro de tenant.
     */
    public function scopeWithoutTenant(Builder $query): Builder
    {
        Log::debug("Removiendo scope tenant para consulta");
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope para consultas de todos los tenants.
     */
    public function scopeAllTenants(Builder $query): Builder
    {
        Log::debug("Consulta todos los tenants");
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope para consultas de un tenant específico.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        Log::debug("Consulta para tenant específico: {$tenantId}");
        return $query->withoutGlobalScope('tenant')->where($query->getModel()->getTable() . '.tenant_id', $tenantId);
    }

    /**
     * Relación con el tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Verificar si pertenece al tenant actual.
     */
    public function belongsToCurrentTenant(): bool
    {
        return $this->tenant_id === TenantManager::getCurrentTenantId();
    }
}