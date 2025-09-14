<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <!-- Brand -->
    <a class="navbar-brand fw-bold text-white fs-4" href="#">Anugerah ASN</a>

    <!-- Toggle Button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto nav-underline">
        <!-- Dashboard -->
        <li class="nav-item d-flex align-items-center me-3">
          <i class="bi bi-house-door text-white me-1"></i>
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
        </li>

        <!-- Users -->
        <li class="nav-item d-flex align-items-center me-3">
          <i class="bi bi-people text-white me-1"></i>
          <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">User</a>
        </li>

        <!-- Teams -->
        <li class="nav-item d-flex align-items-center me-3">
          <i class="bi bi-people-fill text-white me-1"></i>
          <a class="nav-link {{ request()->routeIs('admin.tims.*') ? 'active' : '' }}" href="{{ route('admin.tims.index') }}">Team</a>
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