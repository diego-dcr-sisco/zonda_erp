# Matriz de Planes y Permisos

Fecha de revisión: 10 de abril de 2026

## Alcance

Este documento resume la matriz actual detectada en el código de `zonda_erp` para los planes y permisos por tenant.

Fuentes principales revisadas:

- `database/seeders/PlanSeeder.php`
- `database/seeders/PlanPermissionMappingSeeder.php`
- `database/seeders/TenantPermissionSeeder.php`
- `app/Console/Commands/SyncTenantPermissionsFromPlan.php`
- `app/Traits/HasTenantFilteredPermissions.php`
- `resources/views/dashboard/index.blade.php`
- `routes/web.php`

## Cómo se calcula el acceso real

1. Los planes se crean en `PlanSeeder`.
2. Los permisos por plan se asignan en `PlanPermissionMappingSeeder`.
3. El comando `tenants:sync-permissions` copia esa relación a `tenant_permission_control` con `is_allowed = true|false`.
4. Los helpers `tenant_can()` y `tenant_can_any()` consultan esos permisos filtrados por tenant.
5. Varias rutas todavía usan `can:integral`, lo cual no depende del plan sino del tipo de usuario.

## Matriz actual detectada en código

Convenciones:

- `1`: el plan sí tiene permiso o visibilidad clara.
- `0`: el plan no tiene permiso.
- `NC`: no hay control fino suficiente en código para afirmarlo con precisión, o no existe un permiso dedicado.

| Módulo | Lite | Lite+ | Pro | Starter |
|---|---:|---:|---:|---:|
| 1.1 Clientes y prospectos | 0 | 1 | 1 | 0 |
| 1.2 Seguimientos y cotizaciones | 0 | 1 | 1 | 0 |
| 2.1 Órdenes de servicio | 1 | 1 | 1 | 0 |
| 2.2 Certificados de órdenes de servicio | NC | NC | NC | NC |
| 3.1 Contratos y programación de actividades | 0 | 1 | 1 | 0 |
| 3.2 Configuración de actividades | NC | NC | NC | NC |
| 3.3 Generación automática PDF del contrato | NC | NC | NC | NC |
| 3.4 Renovaciones | NC | NC | NC | NC |
| 3.5 Calendarios de actividades | 0 | 1 | 1 | 0 |
| 4.1 Catálogo de servicios | 1 | 1 | 1 | 0 |
| 4.2 Catálogo de productos | 1 | 1 | 1 | 0 |
| 4.3 Catálogo de plagas | 1 | 1 | 1 | 0 |
| 4.4 Catálogo de lotes | 0 | 1 | 1 | 0 |
| 5.1 Control de inventario | 0 | 1 | 1 | 0 |
| 5.2 Multi-almacén | NC | NC | NC | NC |
| 5.3 Movimientos entre almacenes | NC | NC | NC | NC |
| 5.4 Planes de rotación | NC | NC | NC | NC |
| 5.5 Consumos mensuales | 0 | 0 | 0 | 0 |
| 5.6 Consumos en órdenes | NC | NC | NC | NC |
| 5.7 Estadísticas de consumo | 0 | 0 | 0 | 0 |
| 6. Recursos Humanos | 0 | 0 | 1 | 0 |
| 7.1 Planos digitales | 0 | 0 | 1 | 0 |
| 7.2 Puntos de control | 0 | 0 | 1 | 0 |
| 7.3 Dispositivos con niveles de incidencia | NC | NC | NC | NC |
| 7.4 Simbología en planos | 0 | 0 | 1 | 0 |
| 7.5 Métricas y reportes por dispositivo | 0 | 0 | 0 | 0 |
| 7.6 Generación de QR | NC | NC | NC | NC |
| 8. Portal de clientes | 0 | 1 | 1 | 0 |
| 9. App de los técnicos | NC | NC | NC | NC |
| 10.1 Certificados manuales | 0 | 0 | 0 | 1 |
| 10.2 Cotizaciones manuales | 0 | 0 | 0 | 1 |

## Hallazgos importantes

### 1. No existe el plan Starter

Actualmente el sistema solo crea estos planes:

- `Lite`
- `Lite+`
- `Pro`

No existe el plan `Starter` en la siembra actual.

### 2. Emisión manual ahora está restringida a Starter

La card del dashboard y la validación en backend para certificados/cotizaciones manuales se ajustaron para permitir acceso únicamente cuando el tenant pertenece al plan `Starter`.

### 3. Planificación está habilitada para Lite+

Según el código, `Lite+` ya tiene `show_planning`, por lo que el módulo de planificación está habilitado para ese plan.

### 4. Varias rutas no dependen del plan

Muchas rutas usan `can:integral`, lo que significa que dependen del tipo de usuario (`type_id == 1`) y no del permiso del plan.

Eso puede generar diferencias entre:

- lo que la matriz dice,
- lo que el dashboard muestra,
- y lo que una cuenta realmente puede abrir por URL.

### 5. Hay permisos definidos pero no asignados a ningún plan

Existen permisos en `TenantPermissionSeeder` que no aparecen en `PlanPermissionMappingSeeder`.

Ejemplos:

- `handle_tracking`
- `handle_floorplans`
- `handle_quality`
- `handle_invoice`
- `handle_client_system`
- `handle_rh`
- `show_matrix`
- `show_quality_analytics`
- `show_stock_alerts`
- `assing_technician`
- `generate_voucher_stock`

Si un permiso no está en `plan_permissions`, al sincronizar tenants terminará como `0` en `tenant_permission_control`.

## Archivos clave

- `database/seeders/PlanSeeder.php`
- `database/seeders/PlanPermissionMappingSeeder.php`
- `database/seeders/TenantPermissionSeeder.php`
- `app/Console/Commands/SyncTenantPermissionsFromPlan.php`
- `app/Traits/HasTenantFilteredPermissions.php`
- `app/Providers/AuthServiceProvider.php`
- `resources/views/dashboard/index.blade.php`
- `routes/web.php`

## Recomendación

Antes de ajustar la UI o seguir asignando cuentas por plan, conviene definir una matriz final esperada y después alinear:

1. planes existentes,
2. permisos por plan,
3. cards del dashboard,
4. middleware de rutas,
5. permisos específicos faltantes.