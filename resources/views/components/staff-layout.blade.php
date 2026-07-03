<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/staff-app.css') }}">

    @stack('styles')
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-kalsel.svg') }}">
    <title>Staff — TITIR</title>
</head>

<body class="staff-app">
    <x-navbar></x-navbar>

    <main class="staff-main">
        {{ $slot }}

        <div class="text-muted" style="text-align: center; font-size: 12.5px; margin-top: 32px;">
            © 2025 Naufal Najwan Abdurrafi &amp; M. Rizki Syandana
        </div>
    </main>

    <script src="{{ asset('js/staff-app.js') }}"></script>
    @stack('scripts')
</body>

</html>
