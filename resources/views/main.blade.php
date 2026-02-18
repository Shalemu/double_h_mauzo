<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - DOUBLE H COSMETICS Admin Panel</title>

    @include('components.header')
</head>

<body>

    {{-- Top Menu --}}
    @include('components.mainmenu')

    {{-- Page Content --}}
    <main class="container-fluid" style="margin-top: 70px;">
        @yield('content')
    </main>

    {{-- Scripts --}}
    @stack('scripts')

</body>
</html>
