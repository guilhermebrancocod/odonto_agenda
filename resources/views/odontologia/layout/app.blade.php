<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Odonto')</title>

    <link rel="icon" type="image/png" href="/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

    <!-- CSS global -->
    <link href="/css/style.css" rel="stylesheet">
    <!-- CSS do sidebar -->
    <link href="/css/sidebar.css" rel="stylesheet">

    @stack('styles')
</head>

<body>
    @include('components.sidebar') <!-- Aqui está sua sidebar nova -->

    <div style="margin-left: 224px; padding: 20px;">
        @yield('content')
    </div>

    <!-- Scripts globais -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>
</html>