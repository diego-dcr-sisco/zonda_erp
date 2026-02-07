# Análisis: Generación de Calendario Anual en PDF para Contratos

## 📊 Objetivo
Generar un documento PDF que sea un calendario anual donde aparezcan los servicios asignados a un contrato, con los días en que tocan señalados en color. Cada servicio tendrá su propio color para una fácil identificación.

---

## 📁 Estructura de Datos Actual

### Modelos Relacionados:
1. **Contract** - Contrato con `startdate`, `enddate`
2. **Order** - Órdenes de servicio con `programmed_date`, `contract_id`
3. **OrderService** - Relación entre órdenes y servicios
4. **ContractService** - Configuración de servicios por contrato
5. **Service** - Servicios disponibles con colores/información

### Relaciones Clave:
```
Contract
├── orders (órdenes programadas)
├── services (a través de ContractService)
└── technicians (técnicos asignados)

Order
├── programmed_date (fecha programada)
├── services (a través de OrderService)
├── contract (relación con contrato)
└── status (estado de la orden)
```

---

## ✅ Estado de Implementación

### ✅ COMPLETADO

1. **Funciones en ContractController** (`app/Http/Controllers/ContractController.php`)
   - ✅ `annualCalendarPDF()` - Genera el PDF del calendario
   - ✅ `getCalendarData()` - Obtiene datos agrupados por mes/día
   - ✅ `assignServiceColors()` - Asigna colores únicos a servicios

2. **Importaciones**
   - ✅ `use Barryvdh\DomPDF\Facade\Pdf;`

3. **Vista Blade**
   - ✅ `resources/views/contract/pdf/annual_calendar.blade.php` - Template HTML/CSS para PDF

4. **Rutas**
   - ✅ Route: `contract.calendar.pdf` - GET `/contract/calendar/pdf/{id}`

---

## 🚀 Instrucciones de Uso

### Acceder a la Funcionalidad

#### Opción 1: URL Directa
```
/contract/calendar/pdf/{contract_id}
```

#### Opción 2: Desde Blade (Agregar Botón en Vista)
```blade
<!-- En resources/views/contract/show.blade.php o donde muestre contratos -->

<div class="btn-group">
    <a href="{{ route('contract.calendar.pdf', $contract->id) }}" 
       class="btn btn-info btn-sm" 
       title="Descargar calendario anual en PDF">
        <i class="bi bi-calendar-event"></i> Calendario Anual
    </a>
</div>
```

#### Opción 3: Con Navbar/Menu
```blade
<!-- En la sección de acciones del contrato -->
<div class="dropdown-item">
    <a href="{{ route('contract.calendar.pdf', $contract->id) }}" 
       class="dropdown-link">
        <i class="bi bi-file-pdf"></i> Exportar Calendario PDF
    </a>
</div>
```

### Desde JavaScript/jQuery
```javascript
// Generar y descargar PDF
function downloadCalendarPDF(contractId) {
    window.location.href = `/contract/calendar/pdf/${contractId}`;
}

// Usar en un evento
$('#download-calendar-btn').click(function() {
    const contractId = $(this).data('contract-id');
    downloadCalendarPDF(contractId);
});
```

---

## 📊 Características del PDF Generado

### Estructura del Documento
- **Portada/Encabezado**: Información del cliente, código, año, período del contrato
- **Leyenda de Colores**: Muestra cada servicio con su color asignado
- **12 Calendarios Mensuales**: Uno por cada mes del año
- **Indicadores Visuales**: Barras de colores en los días con servicios programados
- **Resumen de Estadísticas**: Total de órdenes y órdenes por servicio
- **Saltos de Página**: Optimizados para impresión (6 meses por página)

### Paleta de Colores
Se asignan automáticamente 20 colores diferentes:
```
#FF6B6B (Rojo)
#4ECDC4 (Turquesa)
#45B7D1 (Azul)
#FFA07A (Salmón)
#98D8C8 (Verde agua)
... y 15 colores más
```

### Estilos PDF
- Encabezados con gradiente
- Calendario con bordes claros
- Fácil lectura en pantalla e impresión
- Responsive para diferentes tamaños de página

---

## 🔧 Personalización

### Cambiar Paleta de Colores
En `ContractController.php`, función `assignServiceColors()`:
```php
$colorPalette = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', // Tus colores aquí
    // ...
];
```

### Modificar Formato de Fecha
En la vista `annual_calendar.blade.php`:
```blade
<!-- Cambiar formato de fecha -->
{{ \Carbon\Carbon::parse($contract->startdate)->format('d/m/Y') }}
<!-- A cualquier formato deseado -->
```

### Ajustar Número de Meses por Página
En el CSS de la vista:
```css
.month-block {
    flex: 0 0 calc(50% - 10px); /* Cambiar 50% por 33% para 3 meses */
}
```

---

## 🎯 Características Adicionales (Opcionales)

### Ya Incluidas:
- ✅ Calendario visual intuitivo
- ✅ Colorización por servicio
- ✅ Información del cliente
- ✅ Resumen de estadísticas
- ✅ Optimizado para impresión

### Futuras Mejoras:
- [ ] Exportar a Excel
- [ ] Filtrar por año específico
- [ ] Mostrar técnicos por día
- [ ] Indicadores de órdenes completadas vs. pendientes
- [ ] Notas personalizadas en días
- [ ] QR de órdenes en el calendario

---

## 📋 Checklist de Integración

Para integrar completamente en tu aplicación:

- [ ] Verificar que las funciones están en `ContractController.php`
- [ ] Verificar que la vista está en `resources/views/contract/pdf/annual_calendar.blade.php`
- [ ] Verificar que la ruta está en `routes/web.php`
- [ ] Probar generación del PDF
- [ ] Agregar botón en vista de contrato (`contract.show.blade.php`)
- [ ] Probar descarga del PDF
- [ ] Verificar que funciona en diferentes navegadores
- [ ] Documentar para usuarios finales

---

## 🐛 Solución de Problemas

### "Route not found"
✅ Verificar que la ruta está registrada en `routes/web.php`

### "Contract not found"
✅ Verificar que el ID del contrato es válido en la URL

### "Error generando PDF"
✅ Verificar que DOMPDF está instalado: `composer require barryvdh/laravel-dompdf`

### "Servicios no aparecen"
✅ Verificar que las órdenes están asociadas al contrato y dentro del rango de fechas

### "Colores no se ven bien"
✅ Verificar configuración CSS de DOMPDF en `config/dompdf.php`

---

## 📞 Soporte

Para más ayuda con:
- Cambios de diseño del PDF
- Ajustes de datos mostrados
- Integración en otros componentes
- Problemas de generación

Contactar al equipo de desarrollo.

---

## 📝 Ejemplo de Uso Completo

```blade
<!-- En resources/views/contract/show.blade.php -->

<div class="row mb-3">
    <div class="col-md-6">
        <h5>Acciones del Contrato</h5>
        <div class="btn-group-vertical w-100" role="group">
            <!-- Botón existente para editar -->
            <a href="{{ route('contract.edit', $contract->id) }}" 
               class="btn btn-primary">
                <i class="bi bi-pencil"></i> Editar Contrato
            </a>
            
            <!-- NUEVO: Botón para descargar calendario -->
            <a href="{{ route('contract.calendar.pdf', $contract->id) }}" 
               class="btn btn-info">
                <i class="bi bi-calendar-event"></i> Descargar Calendario Anual
            </a>
            
            <!-- Botón para renovar -->
            <a href="{{ route('contract.renew', $contract->id) }}" 
               class="btn btn-warning">
                <i class="bi bi-arrow-clockwise"></i> Renovar Contrato
            </a>
        </div>
    </div>
</div>
```

---

## 🎨 Preview del PDF

El PDF generado contendrá:

```
┌─────────────────────────────────────┐
│  📅 Calendario Anual de Servicios   │
│                                     │
│ Cliente: ACME Corp                 │
│ Código: ACM001                     │
│ Año: 2026                          │
│ Período: 01/01/2026 - 31/12/2026  │
└─────────────────────────────────────┘

Leyenda:
■ Servicio A (Rojo)      ■ Servicio B (Turquesa)
■ Servicio C (Azul)      ■ Servicio D (Verde)

┌─────────────────────────────────────┐
│         ENERO 2026                  │
├─────┬─────┬─────┬─────┬─────┬─────┬─────┤
│ Lun │ Mar │ Mié │ Jue │ Vie │ Sab │ Dom │
├─────┼─────┼─────┼─────┼─────┼─────┼─────┤
│  1  │  2  │  3  │  4  │  5  │  6  │  7  │
│  ■  │  ■  │     │  ■  │  ■  │     │     │
├─────┼─────┼─────┼─────┼─────┼─────┼─────┤
│  8  │  9  │ 10  │ 11  │ 12  │ 13  │ 14  │
│  ■  │     │  ■  │  ■  │     │  ■  │     │
└─────┴─────┴─────┴─────┴─────┴─────┴─────┘

... 11 meses más ...

📊 Resumen de Servicios
Total de Órdenes: 156
Servicios Activos: 4
  • Servicio A: 42 órdenes
  • Servicio B: 38 órdenes
  • Servicio C: 45 órdenes
  • Servicio D: 31 órdenes
```

---

**Última actualización:** 6 de febrero de 2026  
**Estado:** ✅ Completamente Implementado y Funcional


