<?php

namespace App\Policies;

use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;

class TenantResourcePolicy
{
    /**
     * Verificar si el usuario puede ver este recurso
     * (que pertenezca a su tenant)
     */
    public function view(User $user, Model $resource)
    {
        // Si el usuario es superadmin, permitir
        if ($user->is_superAdmin) {
            return true;
        }

        // Verificar que el recurso tenga tenant_id
        if (!isset($resource->tenant_id)) {
            return false;
        }

        // Verificar que el tenant_id del recurso coincida con el del usuario
        return $resource->tenant_id === $user->tenant_id;
    }

    /**
     * Verificar si puede actualizar el recurso
     */
    public function update(User $user, Model $resource)
    {
        return $this->view($user, $resource);
    }

    /**
     * Verificar si puede eliminar el recurso
     */
    public function delete(User $user, Model $resource)
    {
        return $this->view($user, $resource);
    }

    /**
     * Verificar si puede crear recursos (debe tener tenant_id)
     */
    public function create(User $user)
    {
        // Super admins siempre pueden crear
        if ($user->is_superAdmin) {
            return true;
        }

        // Usuario normal debe tener tenant asignado
        return !is_null($user->tenant_id);
    }
}
