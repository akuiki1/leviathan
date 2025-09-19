<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Anugerah ASN</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  @stack('styles') {{-- biar bisa inject css tambahan (misal Select2) --}}

  <style>
    :root {
      --primary-color: #3b82f6;
      --primary-light: #60a5fa;
      --secondary-color: #1f2937;
      --text-color: #4b5563;
      --bg-body: #f1f5f9;
      --bg-card: #ffffff;
      --border-color: #e2e8f0;
      --sidebar-width: 280px;
      --sidebar-margin: 20px;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg-body);
      overflow-x: hidden;
    }

    .d-flex {
      display: flex;
    }

    .sidebar-wrapper {
      position: fixed;
      left: var(--sidebar-margin);
      top: var(--sidebar-margin);
      height: calc(100vh - (var(--sidebar-margin) * 2));
      width: var(--sidebar-width);
      background: var(--bg-card);
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      transition: transform 0.3s ease-in-out;
      z-index: 1050;
    }

    .sidebar {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      color: var(--text-color);
    }

    .sidebar-brand {
      padding: 25px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 20px;
    }

    .sidebar-brand .brand-icon {
      width: 40px;
      height: 40px;
      background: var(--primary-color);
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-size: 20px;
      color: white;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .sidebar-brand .brand-text {
      font-size: 22px;
      font-weight: 700;
      color: var(--secondary-color);
      text-decoration: none;
      vertical-align: middle;
    }

    .sidebar .nav {
      padding: 0 20px;
      flex-grow: 1;
    }

    .sidebar .nav-item {
      margin-bottom: 8px;
    }

    .sidebar .nav-link {
      color: var(--text-color);
      padding: 14px 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      font-size: 15px;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .sidebar .nav-link i {
      font-size: 20px;
      margin-right: 15px;
      width: 25px;
      text-align: center;
    }

    .sidebar .nav-link:hover {
      color: var(--primary-color);
      background-color: rgba(59, 130, 246, 0.05);
    }

    .sidebar .nav-link.active {
      color: white;
      background-color: var(--primary-color);
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }

    .sidebar-user {
      padding: 15px 20px 20px;
      margin: 20px;
      background: var(--bg-body);
      border-radius: 15px;
      border: 1px solid var(--border-color);
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      margin-right: 12px;
    }

    .main-content {
      margin-left: calc(var(--sidebar-width) + (var(--sidebar-margin) * 2));
      transition: margin-left 0.3s;
      min-height: 100vh;
      flex-grow: 1;
      background-color: var(--bg-body);
    }

    .header {
      width: 98%;
      background-color: var(--bg-card);
      border-bottom: 1px solid var(--border-color);
      padding: 20px 30px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      border-radius: 15px;
      margin-bottom: 20px;
      margin-top: var(--sidebar-margin);
    }

    .header h5 {
      font-weight: 600;
      color: var(--secondary-color);
    }

    .content {
      padding: 0 30px 30px;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
      .sidebar-wrapper {
        left: 0;
        top: 0;
        height: 100vh;
        border-radius: 0;
        box-shadow: none;
        transform: translateX(-100%);
      }

      .sidebar-wrapper.show {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .header {
        margin-top: 0;
      }

      .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1040;
      }

      .sidebar-overlay.active {
        display: block;
      }

      .header {
        padding: 15px 20px;
      }

      .content {
        padding: 0 20px 20px;
      }
    }

    /* Styling tambahan untuk konten */
    .stat-card {
      background: var(--bg-card);
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border: none;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      color: white;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>
  <div class="d-flex">
    <div class="sidebar-wrapper" id="sidebar-wrapper">
      <nav class="sidebar">
        <div class="sidebar-brand">
          <span class="brand-icon">
            <i class="bi bi-award"></i>
          </span>
          <span class="brand-text">Anugerah ASN</span>
        </div>

        <ul class="nav flex-column">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
              <i class="bi bi-house-door-fill"></i>
              Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
              <i class="bi bi-person-fill"></i>
              User
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.tims.*') ? 'active' : '' }}" href="{{ route('admin.tims.index') }}">
              <i class="bi bi-people-fill"></i>
              Team
            </a>
          </li>
        </ul>

        <div class="mt-auto">
          <div class="sidebar-user">
            <div class="d-flex align-items-center mb-2">
              <div class="user-avatar">
                @auth
                {{ substr(Auth::user()->name, 0, 1) }}
                @else
                U
                @endauth
              </div>
              <div class="flex-grow-1">
                @auth
                <div class="fw-semibold" style="font-size: 14px; color: var(--secondary-color);">{{ Auth::user()->name }}</div>
                <div class="text-secondary" style="font-size: 12px;">Administrator</div>
                @endauth
              </div>
            </div>
            @auth
            <form action="{{ route('logout') }}" method="POST" class="d-block">
              @csrf
              <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </button>
            </form>
            @endauth
          </div>
        </div>
      </nav>
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="main-content flex-fill d-flex flex-column">
      <header class="header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <button class="btn btn-light d-md-none me-3" id="sidebarToggle">
            <i class="bi bi-list fs-5"></i>
          </button>
          <h5 class="mb-0">Admin Panel</h5>
        </div>
      </header>

      <main class="content flex-fill">
        {{ $slot }}
      </main>

      <x-footer></x-footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts') {{-- biar bisa inject js tambahan (misal Select2) --}}

  <script>
    const sidebarWrapper = document.getElementById("sidebar-wrapper");
    const overlay = document.getElementById("sidebar-overlay");
    const toggleBtn = document.getElementById("sidebarToggle");

    toggleBtn?.addEventListener("click", () => {
      sidebarWrapper.classList.toggle("show");
      overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", () => {
      sidebarWrapper.classList.remove("show");
      overlay.classList.remove("active");
    });

    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
          sidebarWrapper.classList.remove("show");
          overlay.classList.remove("active");
        }
      });
    });
  </script>
</body>

</html>