<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventori & Pengadaan — PT Prima Mitrainti Sejati')</title>

    <!-- AdminLTE 3 CSS (via CDN untuk development; produksi disarankan via npm build) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <style>
        .content-wrapper { min-height: 85vh; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('home') }}" class="nav-link">Beranda</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                        <span class="badge badge-light">{{ auth()->user()->roleName() }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                            </button>
                        </form>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link"><i class="fas fa-sign-in-alt"></i> Masuk</a>
                </li>
            @endauth
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('home') }}" class="brand-link">
            <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png"
                 alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Inventori &amp; Pengadaan</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    {{-- Dashboard --}}
                    <li class="nav-item {{ Request::routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    @auth
                    {{-- Master Data (master.view) --}}
                    @if(auth()->user()->hasPermission('master.view'))
                    <li class="nav-header">MASTER DATA</li>

                    <li class="nav-item {{ Request::routeIs('master.items.*') ? 'active' : '' }}">
                        <a href="{{ route('master.items.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Barang</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::routeIs('master.categories.*') ? 'active' : '' }}">
                        <a href="{{ route('master.categories.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>Kategori Barang</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::routeIs('master.units.*') ? 'active' : '' }}">
                        <a href="{{ route('master.units.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-balance-scale"></i>
                            <p>Satuan</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::routeIs('master.suppliers.*') ? 'active' : '' }}">
                        <a href="{{ route('master.suppliers.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-truck"></i>
                            <p>Supplier</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::routeIs('master.warehouses.*') || Request::routeIs('master.locations.*') ? 'active' : '' }}">
                        <a href="{{ route('master.warehouses.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>Gudang &amp; Lokasi</p>
                        </a>
                    </li>

                    <li class="nav-item {{ Request::routeIs('master.projects.*') ? 'active' : '' }}">
                        <a href="{{ route('master.projects.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-project-diagram"></i>
                            <p>Proyek</p>
                        </a>
                    </li>
                    @endif

                    {{-- Pengaturan (super_admin) --}}
                    @if(auth()->user()->hasPermission('user.manage') || auth()->user()->hasPermission('role.manage') || auth()->user()->hasPermission('audit.view'))
                    <li class="nav-header">PENGATURAN</li>

                    @if(auth()->user()->hasPermission('user.manage'))
                    <li class="nav-item {{ Request::routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Pengguna</p>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('role.manage'))
                    <li class="nav-item {{ Request::routeIs('roles.*') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>Role &amp; Permission</p>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('audit.view'))
                    <li class="nav-item {{ Request::routeIs('audit-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('audit-logs.index') }}" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Audit Log</p>
                        </a>
                    </li>
                    @endif
                    @endif
                    @endauth
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $title ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            @yield('breadcrumb')
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @include('partials.alerts')
                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Versi</b> 1.0.0
        </div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">PT Prima Mitrainti Sejati</a>.</strong> Hak cipta dilindungi.
    </footer>

</div>
<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>