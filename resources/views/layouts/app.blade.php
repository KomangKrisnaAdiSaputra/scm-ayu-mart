<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('titlePage') &mdash; {{ config('app.name') }}</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('../assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('../assets/css/components.css') }}">

    <style>
        .flash-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 340px;
            animation: slideIn 0.4s ease-out;
        }

        .flash-alert:nth-of-type(2) {
            top: 95px;
        }

        .flash-alert:nth-of-type(3) {
            top: 170px;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        input[type="number"] {
            min-width: 90px;
            max-width: 100%;
        }

        textarea {
            height: 50px !important;
        }
    </style>


    </style>
    @yield('css')
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">

                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i
                                    class="fas fa-bars"></i></a></li>
                    </ul>
                </form>

                <ul class="navbar-nav navbar-right">
                    {{-- <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg beep"><i class="far fa-bell"></i></a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right">
                            <div class="dropdown-header">Notifications
                                <div class="float-right">
                                    <a href="#">Mark All As Read</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons">
                                <a href="#" class="dropdown-item dropdown-item-unread">
                                    <div class="dropdown-item-icon bg-primary text-white">
                                        <i class="fas fa-code"></i>
                                    </div>
                                    <div class="dropdown-item-desc">
                                        Template update is available now!
                                        <div class="time text-primary">2 Min Ago</div>
                                    </div>
                                </a>
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li> --}}
                    <li class="dropdown"><a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="{{ asset('../assets/image/user.png') }}"
                                class="rounded-circle mr-1">
                            <div class="d-sm-none d-lg-inline-block">Hi, {{ auth()->user()->nama }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in 5 min ago</div>
                            <a href="#" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> Profile
                            </a>
                            <a href="#" class="dropdown-item has-icon">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="dropdown-item has-icon text-danger d-flex align-items-center"
                                    style="background:none;border:none;width:100%;">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </nav>

            {{-- Sidebar --}}
            <div class="main-sidebar">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="#">SCM</a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="#">SCM</a>
                    </div>
                    <ul class="sidebar-menu">
                        {{-- <li class="menu-header">Dashboard</li> --}}
                        <li class="{{ trim($__env->yieldContent('titlePage')) === 'Dashboard' ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="fas fa-fire"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>

                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Cabang']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li
                                class="{{ trim($__env->yieldContent('titlePage')) === 'Manajemen Produk' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('produk') }}">
                                    <i class="fas fa-boxes"></i>
                                    <span>Manajemen Produk</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if (in_array(auth()->user()->role, ['Manajer']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li
                                class="{{ trim($__env->yieldContent('titlePage')) === 'Manajemen User' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('usermanagement') }}">
                                    <i class="fas fa-users"></i>
                                    <span>Manajemen User</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if (in_array(auth()->user()->role, ['Gudang', 'Cabang']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li
                                class="{{ trim($__env->yieldContent('titlePage')) === 'Permintaan Cabang' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('permintaancabang') }}">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span>Permintaan Cabang</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if (in_array(auth()->user()->role, ['Gudang', 'Cabang', 'Kurir']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li class="{{ trim($__env->yieldContent('titlePage')) === 'Pengiriman' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('pengiriman') }}">
                                    <i class="fas fa-truck"></i>
                                    <span>Pengiriman</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if (in_array(auth()->user()->role, ['Manajer', 'Gudang', 'Supplier']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li
                                class="{{ trim($__env->yieldContent('titlePage')) === 'Purchase Order' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('purchaseorder') }}">
                                    <i class="fas fa-file-invoice"></i>
                                    <span>Purchase Order</span>
                                </a>
                            </li>
                        </ul>

                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li class="{{ trim($__env->yieldContent('titlePage')) === 'Retur' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('retur') }}">
                                    <i class="fas fa-undo-alt"></i>
                                    <span>Retur</span>
                                </a>
                            </li>
                        </ul>
                    @endif

                    @if (in_array(auth()->user()->role, ['Manajer', 'Supplier']))
                        <ul class="sidebar-menu">
                            {{-- <li class="menu-header">Dashboard</li> --}}
                            <li
                                class="{{ trim($__env->yieldContent('titlePage')) === 'Payment List' ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('paymentlist') }}">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Payment List</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                </aside>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1>@yield('titlePage')</h1>
                        @if (isset($breadcrumbs) && count($breadcrumbs) > 0)
                            <div class="section-header-breadcrumb">
                                @foreach ($breadcrumbs as $breadcrumb)
                                    <div class="breadcrumb-item {{ $breadcrumb['active'] }}">
                                        @if (isset($breadcrumb['url']) && $breadcrumb['url'])
                                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                                        @else
                                            {{ $breadcrumb['label'] }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="section-body">
                        @yield('app')
                    </div>
                </section>
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; 2025 <div class="bullet"></div> SCM</a>
                </div>
                <div class="footer-right">
                </div>
            </footer>
        </div>
    </div>

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow flash-alert">
            <i class="fas fa-check-circle mr-1"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- WARNING --}}
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow flash-alert">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- ERROR --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow flash-alert">
            <i class="fas fa-times-circle mr-1"></i>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif


    <!-- General JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('../assets/js/stisla.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- Template JS File -->
    <script src="{{ asset('../assets/js/scripts.js') }}"></script>
    <script src="{{ asset('../assets/js/custom.js') }}"></script>

    <!-- Page Specific JS File -->
    <script src="{{ asset('../assets/js/page/index.js') }}"></script>

    <script>
        document.querySelectorAll('.flash-alert').forEach((alert, index) => {
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 600);
            }, 9000 + (index * 700)); // ±9–10 detik
        });
    </script>

    @yield('js')
</body>

</html>
