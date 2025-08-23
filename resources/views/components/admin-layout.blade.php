<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="d-flex flex-column min-vh-100">

        <!-- Navbar / Topbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.tims.index') }}">Tim</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.honoraria.index') }}">Honor</a></li>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-danger my-2 my-sm-0" type="submit">Logout</button>
                </form>
            </div>
        </nav>

        <!-- Main content -->
        <div class="container-fluid mt-4 flex-fill">
            {{ $slot }}
        </div>

        <!-- Footer -->
        <x-footer></x-footer>

    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>

</html>
