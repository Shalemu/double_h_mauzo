<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - DOUBLE H COSMETICS Admin Panel</title>

    @include('components.header')

    <style>
        /* GLOBAL LOADER */
        #global-loader{
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(255,255,255,0.85);
            display:none;
            justify-content:center;
            align-items:center;
            z-index:99999;
        }

        .loader-content{
            text-align:center;
            font-weight:600;
            color:#333;
        }

        .spinner{
            width:60px;
            height:60px;
            border:6px solid #e3e3e3;
            border-top:6px solid #007bff;
            border-radius:50%;
            animation:spin 1s linear infinite;
            margin:auto;
        }

        @keyframes spin{
            0%{transform:rotate(0deg);}
            100%{transform:rotate(360deg);}
        }
    </style>
</head>

<body>

{{-- Top Menu --}}
@include('components.mainmenu')

{{-- Page Content --}}
<main class="container-fluid" style="margin-top:70px;">
    @yield('content')
</main>

{{-- GLOBAL LOADER --}}
<div id="global-loader">
    <div class="loader-content">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
</div>

{{-- Scripts --}}
@stack('scripts')

<script>
document.addEventListener("DOMContentLoaded", function(){

    function showLoader(){
        const loader = document.getElementById('global-loader');
        if(loader) loader.style.display = 'flex';
    }

    function hideLoader(){
        const loader = document.getElementById('global-loader');
        if(loader) loader.style.display = 'none';
    }

    /* SHOW SPINNER ON PAGE NAVIGATION */
    document.querySelectorAll('a').forEach(link=>{
        link.addEventListener('click',function(){
            let href = this.getAttribute('href');

            if(href && !href.startsWith('#') && !href.startsWith('javascript')){
                showLoader();
            }
        });
    });

    /* SHOW SPINNER ON FORM SUBMIT */
    document.querySelectorAll('form').forEach(form=>{
        form.addEventListener('submit',function(){
            showLoader();
        });
    });

    /* AJAX SUPPORT (jQuery) */
    if(window.jQuery){
        $(document).ajaxStart(function(){
            showLoader();
        });

        $(document).ajaxStop(function(){
            hideLoader();
        });
    }

});
</script>

</body>
</html>