<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark d-flex justify-content-between align-items-center px-3">
  <div>
    <button class="btn text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" style="border:none; background:transparent;">
      <span class="navbar-toggler-icon"></span>
    </button>
    <span class="ms-2 fw-bold">Admin</span>
  </div>
  <div class="d-flex align-items-center">
    <!-- Foto profil user -->
    <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" alt="Profile" class="rounded-circle me-2" width="32" height="32">
    <!-- Tombol logout -->
    <form action="{{ route('logout') }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-outline-danger btn-sm" title="Logout">
        <i class="bi bi-box-arrow-right"></i>   Logout
      </button>
    </form>
  </div>
</nav>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu Admin</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="{{ route('dashboard') }}">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="{{ route('admin.users.index') }}">
          <i class="bi bi-people me-2"></i> Users
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="{{ route('admin.tims.index') }}">
          <i class="bi bi-diagram-3 me-2"></i> Tim
        </a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link text-white" href="{{ route('admin.honoraria.index') }}">
          <i class="bi bi-cash-stack me-2"></i> Honor
        </a>
      </li>
    </ul>
  </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>