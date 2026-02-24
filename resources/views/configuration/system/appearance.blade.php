@extends('layouts.app')
@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center border-bottom ps-4 p-2">
            <span class="text-black fw-bold fs-4">
                APARIENCIA DEL REPORTE DE ORDEN
            </span>
        </div>

        <div class="appearance-container">            
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Configuración del Reporte:</strong> Los cambios que realices aquí se aplicarán automáticamente al reporte PDF que se genera al completar una orden de servicio. Los colores configurados afectarán:
                <ul class="mb-0 mt-2">
                    <li><strong>Encabezados de sección</strong> (Color Principal)</li>
                    <li><strong>Tablas de productos y dispositivos</strong> (Color Secundario)</li>
                    <li><strong>Logo de la empresa</strong> (aparece en la parte superior)</li>
                    <li><strong>Marca de agua de fondo</strong> (con opacidad ajustable)</li>
                </ul>
            </div>

            <!-- Indicador de Colores Guardados -->
            <div class="settings-card mb-3">
                <div class="settings-card-header">
                    <i class="bi bi-palette-fill me-2"></i>Colores Actualmente Guardados
                </div>
                <div class="settings-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="saved-color-display">
                                <label class="form-label mb-2"><strong>Color Principal:</strong></label>
                                <div class="d-flex align-items-center">
                                    <div class="saved-color-circle" style="background-color: {{ $appearance->primary_color ?? '#cccccc' }};"></div>
                                    <span class="ms-3">
                                        <strong>{{ $appearance->primary_color ?? 'Sin configurar' }}</strong>
                                        @if($appearance->primary_color)
                                            <br><small class="text-muted">Este color se usa en encabezados y títulos</small>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="saved-color-display">
                                <label class="form-label mb-2"><strong>Color Secundario:</strong></label>
                                <div class="d-flex align-items-center">
                                    <div class="saved-color-circle" style="background-color: {{ $appearance->secondary_color ?? '#cccccc' }};"></div>
                                    <span class="ms-3">
                                        <strong>{{ $appearance->secondary_color ?? 'Sin configurar' }}</strong>
                                        @if($appearance->secondary_color)
                                            <br><small class="text-muted">Este color se usa en tablas y elementos decorativos</small>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('config.appearance.update') }}" method="POST" enctype="multipart/form-data" id="appearance-form">
                @csrf
                @method('PUT')
                
                <!-- Sección de Logo -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-image me-2"></i>Logo del reporte
                    </div>
                    <div class="settings-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p>Suba una nueva imagen para cambiar el logo que aparece en el reporte de orden. Formato recomendado: PNG. Tamaño máximo: 2MB. Dimensiones recomendadas: 300x110px</p>
                                
                                <label for="logo-upload" class="custom-file-upload">
                                    <i class="bi bi-cloud-upload me-2"></i>Seleccionar Imagen
                                </label>
                                <input id="logo-upload" name="logo" type="file" accept="image/*"/>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="reset-logo" name="reset_logo">
                                    <label class="form-check-label" for="reset-logo">
                                        Restablecer logo predeterminado
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="logo-preview">
                                    @if($appearance->logo_path && file_exists(public_path($appearance->logo_path)))
                                        <img src="{{ asset($appearance->logo_path) }}" alt="Vista previa del logo" id="logo-preview-img">
                                    @else
                                        <div class="no-image-placeholder">
                                            <i class="bi bi-image" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="text-muted mt-2">Sin logo</p>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Vista previa del logo actual</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Marca de Agua -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-droplet me-2"></i>Marca de Agua del reporte
                    </div>
                    <div class="settings-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <p>Suba una imagen para utilizar como marca de agua en el fondo del reporte. Formato recomendado: PNG transparente. Tamaño máximo: 2MB.</p>
                                
                                <label for="watermark-upload" class="custom-file-upload">
                                    <i class="bi bi-cloud-upload me-2"></i>Seleccionar Marca de Agua
                                </label>
                                <input id="watermark-upload" name="watermark" type="file" accept="image/*"/>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="reset-watermark" name="reset_watermark">
                                    <label class="form-check-label" for="reset-watermark">
                                        Restablecer marca de agua predeterminada
                                    </label>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Opacidad de la marca de agua:</label>
                                    <input type="range" class="form-range border rounded" id="watermark-opacity" name="watermark_opacity" 
                                           min="0" max="100" value="{{( $appearance->watermark_opacity ?? 0.1)*100 }}">
                                    <output for="watermark-opacity" id="opacity-value">{{( $appearance->watermark_opacity ?? 0.1)*100 }}%</output>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="watermark-preview">
                                    @if($appearance->watermark_path && file_exists(public_path($appearance->watermark_path)))
                                        <img src="{{ asset($appearance->watermark_path) }}" alt="Vista previa de la marca de agua" id="watermark-preview-img" style="opacity: {{ ($appearance->watermark_opacity ?? 0.1) }};">
                                    @else
                                        <div class="no-image-placeholder">
                                            <i class="bi bi-droplet" style="font-size: 48px; color: #ccc;"></i>
                                            <p class="text-muted mt-2">Sin marca de agua</p>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted">Vista previa de la marca de agua actual</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de Colores -->
                <div class="settings-card">
                    <div class="settings-card-header">
                        <i class="bi bi-palette me-2"></i>Esquema de Colores del Reporte
                    </div>
                    <div class="settings-card-body">
                        <p class="text-muted mb-3">Los colores se aplicarán a los encabezados, tablas y elementos decorativos del reporte PDF.</p>
                        
                        <div class="row">
                            <!-- Columna Izquierda: Selectores de Color -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Color Principal (Encabezados y títulos)</label>
                                    <div class="d-flex align-items-start justify-content-start mb-3" style="gap: 8px; overflow-x: auto;">
                                        @php
                                            $primaryColor = $appearance->primary_color ?? '#012640';
                                            $colorOptions = [
                                                '#012640' => 'Deep Space Blue',
                                                '#02265A' => 'Deep Navy',
                                                '#0A2986' => 'True Cobalt',
                                                '#512A87' => 'Indigo Velvet',
                                                '#793775' => 'Velvet Purple',
                                                '#B74453' => 'Dusty Mauve',
                                                '#DD513A' => 'Fiery Terracotta'
                                            ];
                                        @endphp
                                        
                                        @foreach ($colorOptions as $color => $name)
                                            <div class="color-item text-center">
                                                <div class="color-option {{ $color == $primaryColor ? 'selected' : '' }}" 
                                                     style="background-color: {{ $color }};" 
                                                     data-color="{{ $color }}"
                                                     data-target="primary"
                                                     title="{{ $name }}">
                                                    @if($color == $primaryColor)
                                                        <i class="bi bi-check-circle-fill text-white" style="font-size: 20px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                                                    @endif
                                                </div>
                                                <small class="color-label">{{ $name }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label">O elegir un color personalizado:</label>
                                        <input type="color" class="form-control form-control-color" id="custom-primary-color" 
                                               name="primary_color" value="{{ $primaryColor }}">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Color Secundario (Tablas y elementos decorativos)</label>
                                    <div class="d-flex align-items-start justify-content-start mb-3" style="gap: 8px; overflow-x: auto;">
                                        @php
                                             $secondaryColor = $appearance->secondary_color ?? '#793775';
                                            $secondaryOptions = [
                                                '#793775' => 'Velvet Purple',
                                                '#B74453' => 'Dusty Mauve',
                                                '#DD513A' => 'Fiery Terracotta',
                                                '#512A87' => 'Indigo Velvet',
                                                '#0A2986' => 'True Cobalt',
                                                '#012640' => 'Deep Space Blue'
                                            ];
                                        @endphp
                                        
                                        @foreach ($secondaryOptions as $color => $name)
                                            <div class="color-item text-center">
                                                <div class="color-option {{ $color == $secondaryColor ? 'selected' : '' }}" 
                                                     style="background-color: {{ $color }};" 
                                                     data-color="{{ $color }}"
                                                     data-target="secondary"
                                                     title="{{ $name }}">
                                                    @if($color == $secondaryColor)
                                                        <i class="bi bi-check-circle-fill text-white" style="font-size: 20px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></i>
                                                    @endif
                                                </div>
                                                <small class="color-label">{{ $name }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label">O elegir un color personalizado:</label>
                                        <input type="color" class="form-control form-control-color" id="custom-secondary-color" 
                                               name="secondary_color" value="{{ $secondaryColor }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Columna Derecha: Vista Previa -->
                            <div class="col-md-6">
                                <div class="preview-box">
                                    <h6 class="mb-3"><i class="bi bi-eye me-2"></i>Vista Previa de Colores</h6>
                                    <p class="text-muted small">Así se verán los colores en el reporte:</p>
                                    <div class="preview-navbar" id="preview-navbar">
                                        <span class="fw-bold" style="color:white">ENCABEZADO DEL REPORTE</span>
                                    </div>
                                    <div class="mt-3">
                                        <span class="fw-light small">Los colores seleccionados se aplicarán a todos los encabezados de sección, tablas de productos y elementos decorativos del reporte PDF.</span>
                                    </div>
                                    <div class="p-3 border rounded mt-3 bg-light">
                                        <p class="mb-2 small"><strong>Ejemplo de elementos:</strong></p>
                                        <button type="button" class="btn btn-sm btn-primary-preview fw-bold">Encabezados</button>
                                        <button type="button" class="btn btn-sm btn-secondary-preview ms-2 fw-bold">Tablas</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex mt-4">
                    <button type="button" class="btn btn-danger me-3" onclick="resetForm()">Cancelar</button>
                    <button type="submit" class="btn btn-primary me-3">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .appearance-container {
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fc;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .settings-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .saved-color-display {
            padding: 10px;
        }
        
        .saved-color-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        
        .settings-card-header {
            background-color: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            color: #4e73df;
        }
        
        .settings-card-body {
            padding: 20px;
        }
        
        .logo-preview, .watermark-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            overflow: hidden;
            background-color: #f8f9fc;
            transition: all 0.3s;
        }
        
        .logo-preview:hover, .watermark-preview:hover {
            border-color: #4e73df;
            background-color: #eaecf4;
        }
        
        .logo-preview img, .watermark-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .no-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
        }
        
        .color-item {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            min-width: 60px;
        }
        
        .color-label {
            display: block;
            font-size: 9px;
            line-height: 1.2;
            margin-top: 5px;
            word-wrap: break-word;
            max-width: 60px;
            text-align: center;
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-block;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
            flex-shrink: 0;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .color-option:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .color-option.selected {
            transform: scale(1.15);
            border: 3px solid #000;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #000, 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .custom-file-upload {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4e73df;
            color: white;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        
        .custom-file-upload:hover {
            background-color: #3a5ccc;
        }
        
        /* .btn-save {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
            border-radius: 5px;
        } */
        
        .btn-save:hover {
            background-color: #3a5ccc;
            transform: translateY(-2px);
        }
        
        .preview-box {
            background-color: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            border: 2px solid #e3e6f0;
            height: 100%;
            min-height: 400px;
        }
        
        .preview-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .preview-navbar {
            background-color: #4e73df;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .form-control-color {
            width: 60px;
            height: 40px;
        }
        
        .form-range {
            width: 100%;
        }
        
        output {
            display: inline-block;
            margin-left: 10px;
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Previsualización de imagen seleccionada (logo)
            document.getElementById('logo-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewContainer = document.querySelector('.logo-preview');
                        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Vista previa del logo" id="logo-preview-img">`;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Previsualización de imagen seleccionada (marca de agua)
            document.getElementById('watermark-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewContainer = document.querySelector('.watermark-preview');
                        const opacityValue = document.getElementById('watermark-opacity').value / 100;
                        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Vista previa de la marca de agua" id="watermark-preview-img" style="opacity: ${opacityValue};">`;
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Controlador de opacidad
            const opacitySlider = document.getElementById('watermark-opacity');
            const opacityOutput = document.getElementById('opacity-value');
            
            opacitySlider.addEventListener('input', function() {
                const opacityValue = this.value;
                opacityOutput.textContent = opacityValue + '%';
                document.getElementById('watermark-preview-img').style.opacity = opacityValue / 100;
            });
            
            // Selección de colores predefinidos
            document.querySelectorAll('.color-option').forEach(option => {
                option.addEventListener('click', function() {
                    const color = this.getAttribute('data-color');
                    const target = this.getAttribute('data-target');
                    
                    // Quitar selección anterior y sus íconos
                    document.querySelectorAll(`.color-option[data-target="${target}"]`).forEach(el => {
                        el.classList.remove('selected');
                        // Remover ícono de check si existe
                        const existingIcon = el.querySelector('.bi-check-circle-fill');
                        if (existingIcon) {
                            existingIcon.remove();
                        }
                    });
                    
                    // Marcar como seleccionado
                    this.classList.add('selected');
                    
                    // Agregar ícono de check
                    const checkIcon = document.createElement('i');
                    checkIcon.className = 'bi bi-check-circle-fill text-white';
                    checkIcon.style.cssText = 'font-size: 20px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);';
                    this.appendChild(checkIcon);
                    
                    // Actualizar el input de color
                    document.getElementById(`custom-${target}-color`).value = color;
                    
                    // Actualizar vista previa
                    updatePreview();
                });
            });
            
            // Cambio de color personalizado
            document.getElementById('custom-primary-color').addEventListener('input', updatePreview);
            document.getElementById('custom-secondary-color').addEventListener('input', updatePreview);
            
            // Función para determinar si un color es oscuro
            function isColorDark(hexColor) {
                // Remover el # si existe
                hexColor = hexColor.replace('#', '');
                
                // Convertir hex a RGB
                const r = parseInt(hexColor.substr(0, 2), 16);
                const g = parseInt(hexColor.substr(2, 2), 16);
                const b = parseInt(hexColor.substr(4, 2), 16);
                
                // Calcular luminosidad
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                
                // Si la luminosidad es menor a 0.5, es un color oscuro
                return luminance < 0.5;
            }
            
            function updatePreview() {
                const primaryColor = document.getElementById('custom-primary-color').value;
                const secondaryColor = document.getElementById('custom-secondary-color').value;
                
                // Determinar el color del texto basado en el fondo
                const primaryTextColor = isColorDark(primaryColor) ? '#ffffff' : '#000000';
                const secondaryTextColor = isColorDark(secondaryColor) ? '#ffffff' : '#000000';
                
                // Actualizar barra de navegación en vista previa
                const previewNavbar = document.getElementById('preview-navbar');
                previewNavbar.style.backgroundColor = primaryColor;
                previewNavbar.style.color = primaryTextColor;
                
                // Actualizar botones en vista previa
                const primaryButtons = document.querySelectorAll('.btn-primary-preview');
                primaryButtons.forEach(btn => {
                    btn.style.backgroundColor = primaryColor;
                    btn.style.borderColor = primaryColor;
                    btn.style.color = primaryTextColor;
                });
                
                const secondaryButtons = document.querySelectorAll('.btn-secondary-preview');
                secondaryButtons.forEach(btn => {
                    btn.style.backgroundColor = secondaryColor;
                    btn.style.borderColor = secondaryColor;
                    btn.style.color = secondaryTextColor;
                });
            }
            
            // Inicializar vista previa
            updatePreview();
        });
        
        function resetForm() {
            document.getElementById('appearance-form').reset();
            // Recargar la página para restaurar valores originales
            location.reload();
        }
    </script>
@endsection