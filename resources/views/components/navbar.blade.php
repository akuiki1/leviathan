@php
    use Illuminate\Support\Facades\Auth;

    $isBeranda = request()->routeIs('staff.dashboard.index');
    $isPtatk = request()->routeIs('staff.tim.index');
    $isBuat = request()->routeIs('staff.tim.create');
    $isProfil = request()->routeIs('staff.profile.index');
@endphp

<header class="staff-topbar">
    <div class="staff-topbar-inner">
        <a href="{{ route('staff.dashboard.index') }}" class="staff-logo">
            <div class="staff-logo-mark">A</div>
            <div class="staff-logo-text">Anugerah ASN</div>
        </a>

        {{-- Desktop nav --}}
        <div class="staff-desktop-only" style="align-items: center; flex: 1; gap: 20px;">
            <nav aria-label="Navigasi utama" class="staff-nav">
                <a href="{{ route('staff.dashboard.index') }}" class="staff-nav-item {{ $isBeranda || $isBuat ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('staff.tim.index') }}" class="staff-nav-item {{ $isPtatk ? 'active' : '' }}">PTATK</a>
                <a href="{{ route('staff.profile.index') }}" class="staff-nav-item {{ $isProfil ? 'active' : '' }}">Profil</a>
            </nav>
            <div class="staff-spacer"></div>
            <a href="{{ route('staff.tim.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
                Buat Tim
            </a>
            <div class="staff-divider-v"></div>
            <a href="{{ route('staff.profile.index') }}" title="Profil saya" class="staff-profile-btn">
                <div class="avatar avatar-34">{{ Auth::user()->initials }}</div>
                <div>
                    <div class="staff-profile-name">{{ Auth::user()->name }}</div>
                    <div class="staff-profile-role">{{ Auth::user()->jabatan->name ?? '-' }}</div>
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn-icon" title="Keluar" aria-label="Keluar">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
                </button>
            </form>
        </div>

        {{-- Mobile: spacer + avatar --}}
        <div class="staff-mobile-only" style="flex: 1; justify-content: flex-end; align-items: center;">
            <a href="{{ route('staff.profile.index') }}" aria-label="Profil saya">
                <div class="avatar avatar-36">{{ Auth::user()->initials }}</div>
            </a>
        </div>
    </div>
</header>

{{-- Bottom nav (mobile only) --}}
<nav aria-label="Navigasi bawah" class="staff-bottomnav">
    <a href="{{ route('staff.dashboard.index') }}" class="staff-bottomnav-item {{ $isBeranda ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><path d="M9 22V12h6v10"></path></svg>
        <span class="staff-bottomnav-label">Beranda</span>
    </a>
    <a href="{{ route('staff.tim.create') }}" aria-label="Buat Tim" class="staff-bottomnav-item staff-bottomnav-fab-wrap {{ $isBuat ? 'active' : '' }}">
        <div class="staff-bottomnav-fab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"></path></svg>
        </div>
        <span class="staff-bottomnav-label">Buat Tim</span>
    </a>
    <a href="{{ route('staff.tim.index') }}" class="staff-bottomnav-item {{ $isPtatk ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span class="staff-bottomnav-label">PTATK</span>
    </a>
</nav>
