<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SoundGuard') — Sistema IoT Ruido</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Vite HMR -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="material-icons" style="color:#80DEEA;font-size:26px">sensors</span>
                <h5>SoundGuard</h5>
            </div>
            <small>Sistema IoT — Monitoreo de Ruido</small>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="material-icons">dashboard</span> Dashboard
            </a>
            <a href="{{ route('monitoreo.index') }}" class="{{ request()->routeIs('monitoreo.*') ? 'active' : '' }}">
                <span class="material-icons">graphic_eq</span> Monitoreo en Tiempo Real
            </a>

            <div class="nav-section">Gestión</div>
            <a href="{{ route('alertas.index') }}" class="{{ request()->routeIs('alertas.*') ? 'active' : '' }}">
                <span class="material-icons">notifications_active</span> Alertas
            </a>
            <a href="{{ route('obras.index') }}" class="{{ request()->routeIs('obras.*') ? 'active' : '' }}">
                <span class="material-icons">construction</span> Obras / Áreas
            </a>
            <a href="{{ route('reportes.index') }}" class="{{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <span class="material-icons">assessment</span> Reportes
            </a>
            <a href="{{ route('sensores.index') }}" class="{{ request()->routeIs('sensores.*') ? 'active' : '' }}">
                <span class="material-icons">device_hub</span> Sensores
            </a>

            <div class="nav-section">Administración</div>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <span class="material-icons">manage_accounts</span> Usuarios
            </a>
            <a href="{{ route('configuracion.index') }}"
                class="{{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                <span class="material-icons">settings</span> Configuración
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ ucfirst(Auth::user()->rol) }}</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Topbar -->
    <header id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-md-none" id="sidebarToggle">
                <span class="material-icons">menu</span>
            </button>
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
            <div class="position-relative" style="cursor:pointer"
                onclick="window.location='{{ route('alertas.index') }}'">
                <span class="material-icons text-secondary">notifications</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                    <span class="material-icons" style="font-size:18px">logout</span>
                    <span class="d-none d-md-inline">Salir</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main -->
    <main id="main">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                <span class="material-icons">check_circle</span> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
                <span class="material-icons">error</span> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>

</html>
