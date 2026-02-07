{{-- 
    Ejemplo de Integración del Calendario Anual en la Vista de Contrato
    Archivo: resources/views/contract/show.blade.php
    
    Este archivo muestra cómo agregar el botón de descarga del calendario
    en diferentes secciones de la interfaz
--}}

@extends('layouts.app')
@section('content')
    @php
        if (!function_exists('isPDF')) { 
            function isPDF($filePath)
            {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $extension = strtolower($extension);
                return $extension === 'pdf' || $extension == 'PDF';
            }
        }
    @endphp

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }

        .flat-btn {
            background-color: #FF6B35;
        }

        /* Estilos adicionales para el calendario */
        .calendar-btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .calendar-btn-group .btn {
            flex: 1;
            min-width: 150px;
        }

        @media (max-width: 768px) {
            .calendar-btn-group {
                flex-direction: column;
            }

            .calendar-btn-group .btn {
                width: 100%;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="row p-3 border-bottom">
            <a href="#" onclick="history.back(); return false;" class="col-auto btn-primary p-0 fs-3">
                <i class="bi bi-arrow-left m-3"></i>
            </a>
            <h1 class="col-auto fs-2 fw-bold m-0">
                {{ __('contract.title.show') }} {{ $contract->id }} 
                [ {{ $contract->customer->name }} ]
            </h1>
        </div>

        {{-- NUEVA SECCIÓN: Acciones del Contrato --}}
        <div class="m-3">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">📋 Acciones del Contrato</h5>
                </div>
                <div class="card-body">
                    <div class="calendar-btn-group">
                        {{-- Botón: Editar Contrato --}}
                        <a href="{{ route('contract.edit', $contract->id) }}" 
                           class="btn btn-primary" 
                           title="Editar información del contrato">
                            <i class="bi bi-pencil"></i> Editar Contrato
                        </a>

                        {{-- NUEVO: Botón: Descargar Calendario Anual --}}
                        <a href="{{ route('contract.calendar.pdf', $contract->id) }}" 
                           class="btn btn-info" 
                           title="Descargar calendario anual en PDF"
                           target="_blank">
                            <i class="bi bi-calendar-event"></i> Calendario Anual
                        </a>

                        {{-- Botón: Renovar Contrato --}}
                        <a href="{{ route('contract.renew', $contract->id) }}" 
                           class="btn btn-warning" 
                           title="Renovar contrato para el próximo período">
                            <i class="bi bi-arrow-clockwise"></i> Renovar
                        </a>

                        {{-- Botón: Ver Órdenes --}}
                        <a href="{{ route('contract.searchOrders', $contract->id) }}" 
                           class="btn btn-secondary" 
                           title="Ver todas las órdenes de este contrato">
                            <i class="bi bi-list-ul"></i> Ver Órdenes
                        </a>

                        {{-- Botón: Eliminar (Peligro) --}}
                        <a href="{{ route('contract.destroy', $contract->id) }}" 
                           class="btn btn-danger" 
                           onclick="return confirm('¿Está seguro de eliminar este contrato?');"
                           title="Eliminar contrato de manera permanente">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mensajes de Alerta --}}
        <div class="m-3">
            @include('messages.alert')
        </div>

        {{-- Información del Contrato --}}
        <div class="m-3">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">📄 Información del Contrato</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> {{ $contract->customer->name }}</p>
                            <p><strong>Código:</strong> {{ $contract->customer->code }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Período:</strong> 
                                {{ \Carbon\Carbon::parse($contract->startdate)->format('d/m/Y') }} 
                                a 
                                {{ \Carbon\Carbon::parse($contract->enddate)->format('d/m/Y') }}
                            </p>
                            <p><strong>Estado:</strong> 
                                @if($contract->status == 1)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Información Calendario --}}
        <div class="m-3">
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="bi bi-info-circle"></i> 📅 Calendario Anual
                </h5>
                <p>
                    Descarga el calendario anual que muestra todos los servicios programados 
                    para este contrato. El calendario incluye:
                </p>
                <ul>
                    <li>Todos los 12 meses del año</li>
                    <li>Servicios señalados con colores diferentes</li>
                    <li>Información del cliente y período del contrato</li>
                    <li>Resumen de estadísticas de órdenes</li>
                    <li>Leyenda de colores por servicio</li>
                </ul>
            </div>
        </div>

        {{-- Tabla de Órdenes --}}
        <div class="m-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">📋 Órdenes de Servicio</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        @include('contract.tables.orders')
                    </div>
                </div>
            </div>
        </div>

        {{-- Paginación --}}
        <div class="m-3">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- Script para descargas directas (opcional) --}}
    <script>
        // Función para descargar calendario en nueva pestaña
        function downloadCalendarPDF(contractId) {
            const url = `{{ route('contract.calendar.pdf', '__ID__') }}`.replace('__ID__', contractId);
            window.open(url, '_blank');
        }

        // Función para descargar calendario directamente
        function downloadCalendarDirect(contractId) {
            const url = `{{ route('contract.calendar.pdf', '__ID__') }}`.replace('__ID__', contractId);
            window.location.href = url;
        }

        // Escuchar evento personalizado si es necesario
        document.addEventListener('DOMContentLoaded', function() {
            // Aquí puede agregar lógica adicional si es necesario
            console.log('Página de contrato cargada');
        });
    </script>
@endsection

{{--
    INSTRUCCIONES DE USO:

    1. UBICAR ESTE ARCHIVO EN:
       resources/views/contract/show.blade.php

    2. CARACTERÍSTICAS INCLUIDAS:
       ✅ Botón para descargar calendario anual
       ✅ Botón para editar contrato
       ✅ Botón para renovar contrato
       ✅ Botón para ver órdenes
       ✅ Botón para eliminar contrato
       ✅ Información del contrato
       ✅ Información sobre el calendario
       ✅ Tabla de órdenes
       ✅ Estilos responsive

    3. PERSONALIZACIÓN:

       a) Cambiar texto del botón:
          <a href="{{ route('contract.calendar.pdf', $contract->id) }}">
              Tu texto aquí
          </a>

       b) Cambiar colores:
          class="btn btn-info"  → Otros: btn-primary, btn-success, etc.

       c) Cambiar ícono:
          <i class="bi bi-calendar-event"></i>  → Ver https://icons.getbootstrap.com/

       d) Agregar tooltip:
          title="Tu texto aquí"

       e) Abrir en nueva pestaña:
          target="_blank"

    4. ESTILOS RESPONSIVE:
       - En móviles: Botones apilados verticalmente
       - En desktop: Botones en fila horizontal
       - Gap automático entre botones

    5. VARIABLES DISPONIBLES:
       $contract        - Objeto del contrato actual
       $orders          - Órdenes paginadas
       $order_status    - Estados disponibles

    6. RUTAS DISPONIBLES:
       route('contract.edit', $contract->id)           - Editar contrato
       route('contract.calendar.pdf', $contract->id)   - Descargar calendario
       route('contract.renew', $contract->id)          - Renovar contrato
       route('contract.destroy', $contract->id)        - Eliminar contrato
       route('contract.searchOrders', $contract->id)   - Ver órdenes

    7. FUNCIONES JAVASCRIPT DISPONIBLES:
       downloadCalendarPDF(contractId)      - Abre PDF en nueva pestaña
       downloadCalendarDirect(contractId)   - Descarga PDF directamente
--}}
