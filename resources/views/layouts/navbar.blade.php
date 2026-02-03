<style>
    .navbar-item {
        color: white;
        text-decoration: none;
        background-color: transparent;
        /* "none" no es válido, usa "transparent" */
        transition: all 0.3s ease;
        /* Suaviza la transición */
        padding: 8px 16px;
        /* Añade espacio interno */
        display: block;
        /* Mejor comportamiento en elementos <a> */

    }

    .navbar-item:hover {
        color: white;
        background-color: #5d6d7e;
        transform: translateX(4px);
        border-radius: 0 5px 5px 0;
    }
</style>

<ul class="nav flex-column">
    @isset($navigation)
        @foreach ($navigation as $key => $nav)
            @if ($nav['permission'] == null || tenant_can($nav['permission']))
                <li class="nav-item">
                    <a class="nav-link navbar-item" href="{{ $nav['route'] }}">{{ $key }}</a>
                </li>
            @endif
        @endforeach
    @endisset
</ul>

