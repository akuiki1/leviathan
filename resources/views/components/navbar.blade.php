@php
    use Illuminate\Support\Facades\Auth;
@endphp

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold text-white fs-4" href="{{ route('staff.dashboard.index') }}">Anugerah ASN</a>

        <!-- Toggle button (mobile) -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ request()->is('staff/dashboard*') ? 'active' : '' }}" href="{{ route('staff.dashboard.index') }}">
                        Beranda
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link fw-medium {{ request()->is('staff/profile*') ? 'active' : '' }}" href="{{ route('staff.profile.index') }}">
                        Profil
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link fw-medium {{ request()->is('staff/tim*') ? 'active' : '' }}" href="{{ route('staff.tim.index') }}">
                        Tim saya
                    </a>
                </li>
            </ul>

            <!-- Auth Buttons -->
            <div class="d-flex align-items-center">
                @auth
                    <span class="mx-auto p-2 text-white">
                        Halo, <strong>{{ Auth::user()->name }}</strong>
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm ms-2">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- Custom CSS -->
<style>
    .nav-link.active {
        color: #ffffff !important; /* Biru bootstrap */
        font-weight: 600;
        border-bottom: 2px solid #fff;
    }
    .navbar-nav .nav-link:hover {
        color: #fff !important;
    }
</style>
