<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - DOUBLE H COSMETICS Admin Panel</title>

    @include('components.header')

    <style>
        /* GLOBAL PAGE-LOAD PROGRESS BAR */
        #global-loader{
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:3px;
            z-index:99999;
            background: transparent;
            pointer-events: none;
            opacity: 0;
            transition: opacity .2s ease;
        }
        #global-loader.is-active{
            opacity: 1;
        }
        #global-loader .bar{
            height:100%;
            width:0%;
            background: linear-gradient(90deg, #a02128, #d4454c);
            box-shadow: 0 0 10px rgba(160,33,40,.6);
            transition: width .4s ease;
        }
        #global-loader.is-active .bar{
            width: 78%;
            transition: width 3.5s cubic-bezier(.1,.6,.2,1);
        }
        #global-loader.is-done .bar{
            width: 100%;
            transition: width .2s ease;
        }
    </style>
</head>

<body>

{{-- GLOBAL PAGE-LOAD PROGRESS BAR --}}
<div id="global-loader"><div class="bar"></div></div>
<script>
(function () {
    var loader = document.getElementById('global-loader');
    if (!loader) return;

    // Start the bar creeping toward ~78% right away
    loader.classList.add('is-active');

    // On full load, snap it to 100% then fade out
    window.addEventListener('load', function () {
        loader.classList.remove('is-active');
        loader.classList.add('is-done');
        setTimeout(function () {
            loader.classList.remove('is-done');
        }, 250);
    });

    // Restart the bar the instant the user navigates away
    window.addEventListener('beforeunload', function () {
        loader.classList.remove('is-done');
        loader.classList.add('is-active');
    });
})();
</script>

{{-- Top Menu --}}
@include('components.mainmenu')

{{-- Page Content --}}
<main class="container-fluid" style="margin-top:70px;">
    @yield('content')
</main>

{{-- Scripts --}}
@stack('scripts')

@include('components.dashboard_scripts')

</body>
</html>
