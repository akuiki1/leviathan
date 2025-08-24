<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="collapse" id="navbarToggleExternalContent" data-bs-theme="dark">
  <div class="bg-dark p-4">
    <h5 class="text-white h4">Menu Admin</h5>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('dashboard') }}">Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('admin.users.index') }}">Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('admin.tims.index') }}">Tim</a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="{{ route('admin.honoraria.index') }}">Honor</a>
      </li>
    </ul>
  </div>
</div>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggleExternalContent" aria-controls="navbarToggleExternalContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>