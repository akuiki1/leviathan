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
                        PTATK
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

<!-- Bottom Navigation (mobile only) -->
<nav class="staff-bottom-nav d-lg-none fixed-bottom bg-dark border-top border-secondary">
    <div class="d-flex justify-content-around align-items-stretch">
        <a href="{{ route('staff.dashboard.index') }}"
            class="bottom-nav-link {{ request()->is('staff/dashboard*') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('staff.tim.create') }}" class="bottom-nav-link bottom-nav-fab">
            <span class="bottom-nav-fab-circle"><i class="bi bi-plus-lg"></i></span>
            <span>Buat Tim</span>
        </a>
        <a href="{{ route('staff.tim.index') }}"
            class="bottom-nav-link {{ request()->is('staff/tim') || request()->is('staff/tim/*') && !request()->is('staff/tim/create') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>Tim</span>
        </a>
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

    /* ===== Bottom navigation (mobile) ===== */
    .staff-bottom-nav {
        z-index: 1030;
        box-shadow: 0 -0.25rem 0.75rem rgba(0, 0, 0, 0.15);
    }
    .staff-bottom-nav .bottom-nav-link {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        padding: 8px 4px;
        min-height: 60px;
        color: rgba(255, 255, 255, 0.65);
        text-decoration: none;
        font-size: 0.72rem;
        line-height: 1.1;
        transition: color .15s ease-in-out;
    }
    .staff-bottom-nav .bottom-nav-link i {
        font-size: 1.25rem;
    }
    .staff-bottom-nav .bottom-nav-link.active,
    .staff-bottom-nav .bottom-nav-link:active {
        color: #fff;
    }
    /* Tombol tengah menonjol (FAB style) */
    .staff-bottom-nav .bottom-nav-fab {
        color: #fff;
    }
    .staff-bottom-nav .bottom-nav-fab-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin-top: -22px;
        border-radius: 50%;
        background-color: #0d6efd;
        box-shadow: 0 0.25rem 0.6rem rgba(13, 110, 253, 0.5);
    }
    .staff-bottom-nav .bottom-nav-fab-circle i {
        font-size: 1.35rem;
    }
    /* Beri ruang di bawah konten agar tidak tertutup bottom-nav */
    @media (max-width: 991.98px) {
        body {
            padding-bottom: 72px;
        }
    }
</style>
