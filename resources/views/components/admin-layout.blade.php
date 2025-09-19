<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Anugerah ASN</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    /* Sidebar styling to match the image */
    .sidebar {
      width: 280px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: #2c3e50;
      color: white;
      transition: transform 0.3s ease-in-out;
      z-index: 1050;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    /* Brand/Logo area */
    .sidebar-brand {
      padding: 20px 25px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 10px;
    }

    .sidebar-brand .brand-icon {
      width: 35px;
      height: 35px;
      background: #3498db;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 12px;
      font-size: 18px;
      color: white;
    }

    .sidebar-brand .brand-text {
      font-size: 20px;
      font-weight: 600;
      color: white;
      text-decoration: none;
      vertical-align: middle;
    }

    /* Navigation items */
    .sidebar .nav {
      padding: 0 15px;
    }

    .sidebar .nav-item {
      margin-bottom: 5px;
    }

    .sidebar .nav-link {
      color: rgba(255, 255, 255, 0.8);
      padding: 12px 15px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .sidebar .nav-link i {
      font-size: 18px;
      margin-right: 12px;
      width: 20px;
      text-align: center;
    }

    .sidebar .nav-link:hover {
      color: white;
      background-color: rgba(255, 255, 255, 0.1);
      transform: translateX(3px);
    }

    .sidebar .nav-link.active {
      color: white;
      background-color: #3498db;
      box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    }

    /* User profile section at bottom */
    .sidebar-user {
      position: absolute;
      bottom: 20px;
      left: 15px;
      right: 15px;
      padding: 15px;
      background: rgba(255,255,255,0.05);
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.1);
    }

    .user-avatar {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      background: #3498db;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      margin-right: 10px;
    }

    /* Main content */
    .main-content {
      margin-left: 280px;
      transition: margin-left 0.3s;
      min-height: 100vh;
      background-color: #f8f9fa;
    }

    .header {
      background-color: white;
      border-bottom: 1px solid #dee2e6;
      padding: 20px 30px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .content {
      padding: 30px;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
      }

      .sidebar-overlay.active {
        display: block;
      }

      .header {
        padding: 15px 20px;
      }

      .content {
        padding: 20px;
      }
    }

    /* Dashboard stats cards */
    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border: 1px solid #e9ecef;
      transition: transform 0.2s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
      margin-bottom: 15px;
    }

    .welcome-card {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
    }
  </style>
</head>

<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
      <!-- Brand -->
      <div class="sidebar-brand">
        <span class="brand-icon">
          <i class="bi bi-award"></i>
        </span>
        <span class="brand-text">Anugerah ASN</span>
      </div>

      <!-- Navigation -->
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-house-door"></i>
            Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
            <i class="bi bi-people"></i>
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

      <!-- User profile at bottom -->
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
            <div class="fw-semibold" style="font-size: 13px;">{{ Auth::user()->name }}</div>
            <div class="text-light opacity-75" style="font-size: 11px;">Administrator</div>
            @endauth
          </div>
        </div>
        @auth
        <form action="{{ route('logout') }}" method="POST" class="d-block">
          @csrf
          <button type="submit" class="btn btn-outline-light btn-sm w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </button>
        </form>
        @endauth
      </div>
    </nav>

    <!-- Overlay mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Main Content -->
    <div class="main-content flex-fill d-flex flex-column">
      <!-- Header -->
      <header class="header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <!-- Hamburger for mobile -->
          <button class="btn btn-outline-secondary d-md-none me-3" id="sidebarToggle">
            <i class="bi bi-list"></i>
          </button>
          <h5 class="mb-0">Admin Panel</h5>
        </div>
      </header>

      <!-- Content -->
      <main class="content flex-fill">
        {{ $slot }}
      </main>

      <!-- Footer -->
      <x-footer></x-footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebar-overlay");
    const toggleBtn = document.getElementById("sidebarToggle");

    toggleBtn?.addEventListener("click", () => {
      sidebar.classList.toggle("show");
      overlay.classList.toggle("active");
    });

    overlay.addEventListener("click", () => {
      sidebar.classList.remove("show");
      overlay.classList.remove("active");
    });

    // Handle navigation clicks for mobile - close sidebar
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
      link.addEventListener('click', function() {
        // Close sidebar on mobile when navigation is clicked
        if (window.innerWidth <= 768) {
          sidebar.classList.remove("show");
          overlay.classList.remove("active");
        }
      });
    });
  </script>
</body>

</html>