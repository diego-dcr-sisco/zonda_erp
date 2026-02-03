    @extends('layouts.app')
    @section('content')
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center border-bottom ps-4 p-2">
                <a href="{{ route('customer.index.sedes') }}" class="text-decoration-none pe-3">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <span class="text-black fw-bold fs-4">
                    GRAFICAS DE LA SEDE </span> <span class="ms-2 fs-4"> {{ $customer->name }}</span>
                </span>
            </div>

            <div class="p-3"></div>
        </div>

    @endsection
