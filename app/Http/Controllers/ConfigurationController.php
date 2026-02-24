<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppearanceSetting;
use App\Tenancy\TenantManager;

class ConfigurationController extends Controller
{
    public function index() {
        return view('configuration.index');
    }

    public function appearance() {
        // Obtener la configuración actual del tenant o crear una por defecto
        // TenantScoped automáticamente filtra por tenant_id
        $appearance = AppearanceSetting::first();
        
        // Obtener el tenant actual para las rutas
        $tenant = TenantManager::getCurrentTenant();
        $tenantFolder = $tenant ? $tenant->company_name : 'default';
        
        if (!$appearance) {
            // Crear una instancia temporal con valores por defecto (no se guarda en BD hasta que el usuario haga cambios)
            $appearance = new AppearanceSetting();
            // Ajustar las rutas por defecto para usar la carpeta del tenant
            $appearance->logo_path = "{$tenantFolder}/images/logo_reporte.png";
            $appearance->watermark_path = "{$tenantFolder}/images/watermark.png";
        }
        
        return view('configuration.system.appearance', compact('appearance'));
    }

    public function updateAppearance(Request $request) {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'watermark' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
        ]);

        // Obtener o crear la configuración del tenant
        // TenantScoped asegura que solo se obtengan/creen registros del tenant actual
        $appearance = AppearanceSetting::first();
        
        if (!$appearance) {
            $appearance = new AppearanceSetting();
            // El tenant_id se asigna automáticamente por TenantScoped al guardar
        }

        // Obtener el tenant actual y su carpeta
        $tenant = TenantManager::getCurrentTenant();
        $tenantFolder = $tenant ? $tenant->company_name : 'default';
        
        // Asegurar que existe el directorio del tenant
        $tenantImagesPath = public_path("{$tenantFolder}/images");
        if (!file_exists($tenantImagesPath)) {
            mkdir($tenantImagesPath, 0755, true);
        }

        // Manejar el reseteo del logo
        if ($request->has('reset_logo')) {
            $appearance->logo_path = "{$tenantFolder}/images/logo_reporte.png";
        }
        // Manejar la carga del logo si se proporciona
        elseif ($request->hasFile('logo')) {
            $logoPath = public_path("{$tenantFolder}/images/logo_reporte.png");

            // Si existe el logo actual, eliminarlo
            if (file_exists($logoPath)) {
                @unlink($logoPath);
            }

            // Guardar el nuevo logo
            $request->file('logo')->move(public_path("{$tenantFolder}/images"), 'logo_reporte.png');
            
            // Guardar la ruta en la base de datos
            $appearance->logo_path = "{$tenantFolder}/images/logo_reporte.png";
        }

        // Manejar el reseteo de la marca de agua
        if ($request->has('reset_watermark')) {
            $appearance->watermark_path = "{$tenantFolder}/images/watermark.png";
        }
        // Manejar la carga de la marca de agua si se proporciona
        elseif ($request->hasFile('watermark')) {
            $watermarkPath = public_path("{$tenantFolder}/images/watermark.png");

            // Si existe la marca de agua actual, eliminarla
            if (file_exists($watermarkPath)) {
                @unlink($watermarkPath);
            }

            // Guardar la nueva marca de agua
            $request->file('watermark')->move(public_path("{$tenantFolder}/images"), 'watermark.png');

            // Guardar la ruta en la base de datos
            $appearance->watermark_path = "{$tenantFolder}/images/watermark.png";
        }

        // Actualizar los colores
        $appearance->primary_color = $request->primary_color;
        $appearance->secondary_color = $request->secondary_color;
        $watermarkOpacity = $request->input('watermark_opacity', 10); 
        $appearance->watermark_opacity = $watermarkOpacity / 100; //Convertir valor a decimal
       
        
        $appearance->save();


        return redirect()->route('config.appearance')->with('success', 'La apariencia del reporte se actualizó correctamente. Los cambios se aplicarán al próximo reporte generado.');
    }
}