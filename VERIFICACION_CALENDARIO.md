# 🧪 GUÍA DE VERIFICACIÓN - Calendario Anual en PDF

## ✅ Checklist de Verificación Pre-Producción

Utiliza este checklist para verificar que todo está funcionando correctamente.

---

## 1️⃣ Verificar Archivos Necesarios

### ✓ Paso 1: Controlador
```bash
# Ejecutar en terminal
grep -n "annualCalendarPDF\|getCalendarData\|assignServiceColors" app/Http/Controllers/ContractController.php
```

**Resultado esperado:**
```
1104:    public function annualCalendarPDF(string $id)
1120:    private function getCalendarData(Contract $contract): array
1147:    private function assignServiceColors($services): array
```

### ✓ Paso 2: Vista Blade
```bash
# Ejecutar en terminal
ls -la resources/views/contract/pdf/annual_calendar.blade.php
```

**Resultado esperado:**
```
-rw-r--r-- 1 user group 12345 Feb  6 10:00 annual_calendar.blade.php
```

### ✓ Paso 3: Ruta
```bash
# Ejecutar en terminal
php artisan route:list | grep calendar
```

**Resultado esperado:**
```
GET|HEAD       /contract/calendar/pdf/{id}   ................... contract.calendar.pdf › ContractController@annualCalendarPDF
```

### ✓ Paso 4: Botón en Vista
```bash
# Ejecutar en terminal
grep -n "bi-bar-chart-fill" resources/views/contract/show.blade.php
```

**Resultado esperado:**
```
39:                    <i class="bi bi-bar-chart-fill"></i>
```

---

## 2️⃣ Verificar Funcionamiento en Navegador

### ✓ Paso 1: Acceder al Contrato
1. Abre tu navegador
2. Ve a: `http://localhost:8000/contract/1` (o el contrato que desees)
3. Debe cargar la página correctamente

### ✓ Paso 2: Verificar Botón
1. Busca el botón en la esquina superior derecha
2. Debe tener el ícono de gráfico
3. Coloca el cursor sobre él: debe mostrar "Descargar calendario anual"

### ✓ Paso 3: Hacer Clic
1. Haz clic en el botón
2. Debe descargar un archivo `.pdf`
3. El archivo debe tener nombre: `calendario_<CODIGO_CLIENTE>_2026.pdf`

### ✓ Paso 4: Abrir PDF
1. Abre el PDF descargado
2. Debe mostrar:
   - Encabezado con información del cliente
   - Leyenda de colores
   - 12 calendarios mensuales (3 por fila)
   - Días coloreados según servicios
   - Resumen de estadísticas

---

## 3️⃣ Verificar Contenido del PDF

### ✓ Verificar Encabezado
El PDF debe mostrar:
```
📅 Calendario Anual de Servicios
Cliente: [Nombre del cliente]
Código: [Código del cliente]
Año: 2026
Período Contrato: DD/MM/YYYY - DD/MM/YYYY
```

### ✓ Verificar Leyenda
Debe listar todos los servicios con sus colores:
```
■ Servicio 1 (Color 1)
■ Servicio 2 (Color 2)
etc...
```

### ✓ Verificar Calendarios
Cada mes debe mostrar:
- Nombre del mes y año
- Días de la semana: L M M J V S D
- Números de días (1-31)
- Días coloreados según servicios

### ✓ Verificar Resumen
Al final debe aparecer:
```
📊 Resumen de Servicios
Total de Órdenes: [número]
Servicios Activos: [número]
  • Servicio 1: [X] órdenes
  • Servicio 2: [X] órdenes
etc...
```

---

## 4️⃣ Verificar Múltiples Contratos

### ✓ Prueba 1: Contrato con Servicios
1. Selecciona un contrato que tenga órdenes programadas
2. Descarga el calendario
3. Verifica que muestra los servicios coloreados

### ✓ Prueba 2: Contrato sin Servicios
1. Selecciona un contrato sin órdenes
2. Descarga el calendario
3. Verifica que no tiene errores (debe mostrar calendarios blancos)

### ✓ Prueba 3: Contrato con Muchos Servicios
1. Selecciona un contrato con múltiples servicios
2. Descarga el calendario
3. Verifica que cada servicio tiene un color diferente

---

## 5️⃣ Verificar Impresión

### ✓ Prueba de Impresión a PDF
1. Abre el PDF descargado
2. Presiona Ctrl+P (o Cmd+P en Mac)
3. Selecciona "Imprimir a PDF"
4. Verifica que se ve bien en la vista previa
5. Guarda como PDF

### ✓ Prueba de Impresión Física
1. Abre el PDF
2. Presiona Ctrl+P (o Cmd+P en Mac)
3. Selecciona tu impresora
4. Verifica en "Preferencias":
   - Tamaño: A4
   - Orientación: Horizontal (Landscape)
5. Imprime una página de prueba

---

## 6️⃣ Verificar Códigos de Error

### ✓ Error: "Route not found"
```
Solución:
1. Ejecutar: php artisan route:cache --clear
2. Ejecutar: php artisan cache:clear
3. Reiniciar el servidor
```

### ✓ Error: "Contract not found"
```
Solución:
1. Verificar que el ID del contrato existe
2. Verificar que el contrato tiene órdenes
3. Revisar logs: tail -f storage/logs/laravel.log
```

### ✓ Error: "View not found"
```
Solución:
1. Verificar archivo existe: ls resources/views/contract/pdf/annual_calendar.blade.php
2. Verificar permisos: chmod 644 resources/views/contract/pdf/annual_calendar.blade.php
3. Ejecutar: php artisan view:clear
```

### ✓ Error: "PDF vacío o sin colores"
```
Solución:
1. Verificar que el contrato tiene servicios: SELECT * FROM contract_service WHERE contract_id = X
2. Verificar que existen órdenes: SELECT * FROM order WHERE contract_id = X
3. Revisar logs de DOMPDF en storage/logs/
```

---

## 7️⃣ Verificar Compatibilidad

### ✓ Navegadores Probados
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### ✓ Sistemas Operativos
- [ ] Windows
- [ ] macOS
- [ ] Linux

### ✓ Dispositivos
- [ ] Desktop
- [ ] Laptop
- [ ] Tablet

---

## 8️⃣ Verificar Rendimiento

### ✓ Tiempo de Generación
```php
// En ContractController::annualCalendarPDF()
$start = microtime(true);
// ... código ...
$end = microtime(true);
echo "Tiempo: " . ($end - $start) . " segundos";
```

**Resultado esperado:** < 2 segundos

### ✓ Tamaño del PDF
- Archivo típico: 500 KB - 2 MB
- Si es mayor: revisar colores y compresión

### ✓ Memoria Utilizada
```bash
# Monitorear en terminal
watch -n 1 'ps aux | grep php'
```

**Resultado esperado:** < 100 MB de RAM

---

## 9️⃣ Checklist Final

Marca las tareas completadas:

- [ ] Archivos en su lugar
- [ ] Rutas registradas
- [ ] Botón visible en interfaz
- [ ] PDF descarga correctamente
- [ ] PDF muestra contenido correcto
- [ ] Encabezado visible
- [ ] Leyenda de colores visible
- [ ] 12 calendarios presentes
- [ ] Días coloreados correctamente
- [ ] Resumen de estadísticas visible
- [ ] Impresión funciona
- [ ] Sin errores en logs
- [ ] Tiempo de generación aceptable
- [ ] Tamaño de PDF razonable
- [ ] Probado en múltiples navegadores
- [ ] Probado con diferentes contratos

---

## 🔟 Documentación para Usuarios

### Para el Usuario Final:
```
1. Abre tu contrato en la plataforma
2. Busca el botón con el ícono de gráfico (esquina superior derecha)
3. Haz clic en el botón
4. Se descargará automáticamente el calendario en PDF
5. Puedes imprimir o compartir el PDF
```

### Para el Administrador:
```
Si el botón no aparece o hay errores:
1. Verificar que el navegador tenga JavaScript habilitado
2. Borrar caché del navegador (Ctrl+Shift+Del)
3. Probar en otro navegador
4. Contactar con IT si persiste
```

---

## 📞 Escalación de Problemas

Si encuentras problemas:

1. **Primera línea:** Revisar checklist arriba
2. **Segunda línea:** Revisar logs en `storage/logs/laravel.log`
3. **Tercera línea:** Contactar al equipo de desarrollo

---

## 📝 Registro de Pruebas

Copia este formato para registrar tus pruebas:

```
Fecha: ___/___/______
Navegador: _______________
Sistema Operativo: _______________
Contrato ID: ___

[ ] Botón visible
[ ] PDF descarga
[ ] Contenido correcto
[ ] Imprime bien
[ ] Sin errores

Notas:
_________________________________
_________________________________

Resultado: ✅ EXITOSO / ❌ FALLIDO
```

---

**Última actualización:** 6 de febrero de 2026  
**Estado:** ✅ Lista para verificación
