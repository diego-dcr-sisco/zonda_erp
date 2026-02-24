# Guía de Seguridad Multi-Tenant

## Protección contra Acceso Cruzado entre Tenants

Esta guía explica cómo evitar que usuarios de un tenant accedan a información de otros tenants modificando URLs.

---

## 1. Uso de Middleware en Rutas

### Aplicar a rutas específicas:
```php
// routes/web.php
Route::middleware(['auth', 'validate.tenant.access'])->group(function () {
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
});
```

---

## 2. Validación en Controladores con Gates

### Ejemplo: Validar acceso a una orden
```php
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        // Validar que la orden pertenezca al tenant del usuario
        if (!Gate::allows('view-tenant-resource', $order)) {
            abort(403, 'No tienes acceso a esta orden.');
        }
        
        return view('orders.show', compact('order'));
    }
}
```

---

## 3. Uso de Helpers de Seguridad

### Ejemplo 1: authorize_tenant_resource()
```php
class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        // Lanza automáticamente 403 si no pertenece al tenant
        authorize_tenant_resource($order);
        
        return view('orders.show', compact('order'));
    }
    
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Validar antes de actualizar
        authorize_tenant_resource($order, 'No puedes modificar esta orden.');
        
        $order->update($request->validated());
        return redirect()->back()->with('success', 'Orden actualizada');
    }
}
```

### Ejemplo 2: authorize_tenant_id()
```php
class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        
        // Validar que el usuario tenga acceso a ese tenant
        authorize_tenant_id($tenantId);
        
        // Generar reporte...
    }
}
```

### Ejemplo 3: belongs_to_user_tenant()
```php
class OrderController extends Controller
{
    public function bulkDelete(Request $request)
    {
        $orderIds = $request->input('order_ids');
        $orders = Order::whereIn('id', $orderIds)->get();
        
        foreach ($orders as $order) {
            // Verificar cada orden antes de eliminar
            if (belongs_to_user_tenant($order)) {
                $order->delete();
            }
        }
        
        return redirect()->back()->with('success', 'Órdenes eliminadas');
    }
}
```

### Ejemplo 4: tenant_query()
```php
class CustomerController extends Controller
{
    public function index()
    {
        // Automáticamente filtra por tenant_id del usuario
        $customers = tenant_query(\App\Models\Customer::class)
            ->where('is_active', true)
            ->paginate(20);
        
        return view('customers.index', compact('customers'));
    }
}
```

---

## 4. Uso con Policies de Laravel

### Registrar Policy:
```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Order::class => OrderPolicy::class,
    Customer::class => CustomerPolicy::class,
];
```

### Crear Policy:
```php
// app/Policies/OrderPolicy.php
class OrderPolicy
{
    public function view(User $user, Order $order)
    {
        // Super admins pueden ver todo
        if ($user->is_superAdmin) {
            return true;
        }
        
        // Usuarios normales solo su tenant
        return $order->tenant_id === $user->tenant_id;
    }
    
    public function update(User $user, Order $order)
    {
        return $this->view($user, $order);
    }
}
```

### Usar en Controlador:
```php
class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        // Valida automáticamente con la policy
        $this->authorize('view', $order);
        
        return view('orders.show', compact('order'));
    }
}
```

---

## 5. Validación en API con JSON Response

```php
class ApiOrderController extends Controller
{
    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        if (!belongs_to_user_tenant($order)) {
            return response()->json([
                'error' => 'No tienes acceso a esta orden',
                'message' => 'Acceso denegado'
            ], 403);
        }
        
        return response()->json($order);
    }
}
```

---

## 6. Validación en Queries Usando Scopes

### Crear scope global en modelo:
```php
// app/Models/Order.php
use App\Tenancy\TenantScoped;

class Order extends Model
{
    use TenantScoped; // Ya filtra automáticamente por tenant_id
    
    // ...
}
```

### Si no usas TenantScoped, crear scope manual:
```php
// app/Models/Order.php
public function scopeTenantScope($query)
{
    $user = auth()->user();
    
    if ($user && !$user->is_superAdmin && $user->tenant_id) {
        return $query->where('tenant_id', $user->tenant_id);
    }
    
    return $query;
}

// Uso en controlador:
$orders = Order::tenantScope()->get();
```

---

## 7. Prevención en Formularios

### Validar tenant_id en Request:
```php
// app/Http/Requests/UpdateOrderRequest.php
public function authorize()
{
    $order = Order::find($this->route('id'));
    
    if (!$order) {
        return false;
    }
    
    return belongs_to_user_tenant($order);
}
```

---

## 8. Logging de Intentos de Acceso Sospechosos

```php
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function show($id)
    {
        $order = Order::findOrFail($id);
        
        if (!belongs_to_user_tenant($order)) {
            Log::warning('Intento de acceso cruzado entre tenants', [
                'user_id' => auth()->id(),
                'user_tenant_id' => auth()->user()->tenant_id,
                'attempted_resource' => 'Order',
                'resource_id' => $id,
                'resource_tenant_id' => $order->tenant_id,
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ]);
            
            abort(403, 'Acceso denegado');
        }
        
        return view('orders.show', compact('order'));
    }
}
```

---

## Resumen de Mejores Prácticas

1. ✅ **Siempre validar** que `$resource->tenant_id === auth()->user()->tenant_id`
2. ✅ **Usar middleware** `validate.tenant.access` en rutas sensibles
3. ✅ **Usar helpers** como `authorize_tenant_resource()` para validación rápida
4. ✅ **Usar TenantScoped trait** en modelos para filtrado automático
5. ✅ **Crear Policies** para recursos críticos (órdenes, facturas, reportes)
6. ✅ **Loggear intentos sospechosos** de acceso cruzado
7. ✅ **Validar en FormRequests** usando método `authorize()`
8. ✅ **Excluir super admins** de las restricciones con `if ($user->is_superAdmin)`

---

## Modelos que ya están protegidos

Los siguientes modelos ya usan `TenantScoped` y filtran automáticamente por tenant:
- AppearanceSetting
- (verifica tus modelos con grep `use TenantScoped`)

Para otros modelos, agrega validación manual en controladores.
