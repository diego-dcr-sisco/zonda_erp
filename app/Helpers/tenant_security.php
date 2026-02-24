<?php

use Illuminate\Support\Facades\Gate;

/**
 * Verifica que un recurso pertenezca al tenant actual
 * Lanza excepción 403 si no coincide
 */
if (!function_exists('authorize_tenant_resource')) {
    function authorize_tenant_resource($resource, string $message = 'No tienes acceso a este recurso.')
    {
        if (!Gate::allows('view-tenant-resource', $resource)) {
            abort(403, $message);
        }
    }
}

/**
 * Verifica que el usuario tenga acceso al tenant_id especificado
 * Lanza excepción 403 si no coincide
 */
if (!function_exists('authorize_tenant_id')) {
    function authorize_tenant_id($tenantId, string $message = 'No tienes acceso a esta información.')
    {
        if (!Gate::allows('access-tenant-data', $tenantId)) {
            abort(403, $message);
        }
    }
}

/**
 * Valida que un recurso pertenezca al tenant del usuario autenticado
 * Retorna true/false sin lanzar excepción
 */
if (!function_exists('belongs_to_user_tenant')) {
    function belongs_to_user_tenant($resource): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Super admins tienen acceso a todo
        if ($user->is_superAdmin) {
            return true;
        }

        // Verificar que el recurso tenga tenant_id
        if (!isset($resource->tenant_id)) {
            return false;
        }

        return $resource->tenant_id === $user->tenant_id;
    }
}

/**
 * Obtiene un query builder con filtro automático de tenant_id
 * Solo muestra registros del tenant actual del usuario
 */
if (!function_exists('tenant_query')) {
    function tenant_query($modelClass)
    {
        $user = auth()->user();
        $query = $modelClass::query();

        // Si no es super admin, filtrar por tenant_id
        if ($user && !$user->is_superAdmin && $user->tenant_id) {
            $query->where('tenant_id', $user->tenant_id);
        }

        return $query;
    }
}
