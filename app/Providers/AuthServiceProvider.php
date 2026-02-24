<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Tenancy\TenantManager;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para usuario Integral (acceso total)
        Gate::define('integral', function ($user) {
            return $user->type_id == 1;
        });

        // Gate para Client (acceso limitado)
        Gate::define('client', function ($user) {
            return $user->type_id == 2;
        });

        // Gate para rutas que solo Integral puede ver
        Gate::define('only-integral', function ($user) {
            return $user->type_id == 1;
        });

        // Gate para verificar que un recurso pertenezca al tenant del usuario
        Gate::define('view-tenant-resource', function ($user, $resource) {
            // Super admins pueden ver todo
            if ($user->is_superAdmin) {
                return true;
            }

            // Verificar que el recurso tenga tenant_id
            if (!isset($resource->tenant_id)) {
                return false;
            }

            // Verificar que coincida con el tenant del usuario
            return $resource->tenant_id === $user->tenant_id;
        });

        // Gate para validar acceso basado en tenant_id desde parámetros
        Gate::define('access-tenant-data', function ($user, $tenantId) {
            // Super admins pueden acceder a cualquier tenant
            if ($user->is_superAdmin) {
                return true;
            }

            // Usuarios normales solo pueden acceder a su propio tenant
            return $user->tenant_id === $tenantId;
        });
    }
}
