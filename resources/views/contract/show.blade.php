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
    </style>

    <div class="container-fluid">
        <div class="row p-3 border-bottom">
            <a href="#" onclick="history.back(); return false;" class="col-auto btn-primary p-0 fs-3">
                <i class="bi bi-arrow-left m-3"></i>
            </a>
            <h1 class="col-auto fs-2 fw-bold m-0">{{ __('contract.title.show') }} {{ $contract->id }} [
                {{ $contract->customer->name }} ] </h1>
            <div class="col-auto ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('contract.calendar.pdf', $contract->id) }}" 
                   class="btn btn-info btn-sm" 
                   title="Descargar calendario anual">
                    <i class="bi bi-bar-chart-fill"></i>
                </a>
            </div>
        </div>
        <div class="m-3">
            @include('messages.alert')
            <div class="table-responsive">
                @include('contract.tables.orders')
            </div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
