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
    <title>Staff — Anugerah ASN</title>
</head>

<body class="staff-app">
    <x-navbar></x-navbar>

    <main class="staff-main">
        {{ $slot }}
    </main>

    <script src="{{ asset('js/staff-app.js') }}"></script>
    @stack('scripts')
</body>

</html>
