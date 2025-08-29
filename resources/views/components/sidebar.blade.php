<nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-lg">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="#">Anugerah ASN</a>

    <!-- Toggle Button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto nav-underline">
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

        <!-- Honor -->
        <li class="nav-item d-flex align-items-center me-3">
          <i class="bi bi-cash-stack text-white me-1"></i>
          <a class="nav-link {{ request()->routeIs('admin.honoraria.*') ? 'active' : '' }}" href="{{ route('admin.honoraria.index') }}">Honor</a>
        </li>
      </ul>

      <!-- Search Form -->
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
