<nav class="navbar bg-dark border-bottom border-body navbar-expand-md" data-bs-theme="dark">
  <div class="container-fluid">
    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="#">Anugerah ASN</a>

    <!-- Toggle Button (hamburger) -->
    <button 
      class="navbar-toggler" 
      type="button" 
      data-bs-toggle="collapse" 
      data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" 
      aria-expanded="false" 
      aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible Menu -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-underline">
        <!-- Dashboard -->
        <li class="nav-item d-flex align-items-center">
          <ion-icon class="me-2 text-danger" size="large" name="home-outline"></ion-icon>
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page" href="{{ route('dashboard') }}">Dashboard</a>
        </li>

        <!-- Users -->
        <li class="nav-item d-flex align-items-center">
          <ion-icon class="me-2" size="large" name="people-outline"></ion-icon>
          <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">User</a>
        </li>

        <!-- Teams -->
        <li class="nav-item d-flex align-items-center">
          <ion-icon class="me-2" size="large" name="people-outline"></ion-icon>
          <a class="nav-link {{ request()->routeIs('admin.tims.*') ? 'active' : '' }}" href="{{ route('admin.tims.index') }}">Team</a>
        </li>

        <!-- Honor -->
        <li class="nav-item d-flex align-items-center">
          <ion-icon class="me-2" size="large" name="people-outline"></ion-icon>
          <a class="nav-link {{ request()->routeIs('admin.honoraria.*') ? 'active' : '' }}" href="{{ route('admin.honoraria.index') }}">Honor</a>
        </li>
      </ul>

      <!-- Search -->
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
