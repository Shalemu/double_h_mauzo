<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title'); ?> - DOUBLE H COSMETICS Admin Panel</title>

    <?php echo $__env->make('components.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <style>
        /* GLOBAL LOADER */
        #global-loader{
            position: fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background: rgba(247,249,252,0.8);
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
            display:none;
            justify-content:center;
            align-items:center;
            z-index:99999;
        }

        .loader-content{
            text-align:center;
            background:#fff;
            padding: 32px 46px;
            border-radius: 18px;
            box-shadow: 0 1.5rem 3.5rem rgba(15,23,42,.14);
            border: 1px solid rgba(15,23,42,.04);
            animation: loader-pop .25s ease;
        }

        .loader-content p{
            margin: 16px 0 0;
            font-weight: 600;
            font-size: .85rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            color:#64748b;
        }

        .pro-spinner{
            width:54px;
            height:54px;
            display:block;
            margin: 0 auto;
            transform-origin: 50% 50%;
            animation: pro-spin 1s cubic-bezier(.55,.15,.45,.85) infinite;
        }
        .pro-spinner-track{
            stroke:#e7ecf3;
        }
        .pro-spinner-arc{
            stroke:#1e88e5;
            stroke-linecap:round;
            stroke-dasharray: 72 200;
        }

        @keyframes  pro-spin{
            100%{transform:rotate(360deg);}
        }
        @keyframes  loader-pop{
            from{opacity:0; transform:scale(.92);}
            to{opacity:1; transform:scale(1);}
        }
    </style>
</head>

<body>


<div id="global-loader">
    <div class="loader-content">
        <svg class="pro-spinner" viewBox="0 0 40 40">
            <circle class="pro-spinner-track" cx="20" cy="20" r="17" fill="none" stroke-width="4"></circle>
            <circle class="pro-spinner-arc" cx="20" cy="20" r="17" fill="none" stroke-width="4"></circle>
        </svg>
        <p>Loading...</p>
    </div>
</div>
<script>
(function () {
    var loader = document.getElementById('global-loader');
    if (!loader) return;

    // Show it while this page is still loading
    loader.style.display = 'flex';

    // Hide the loader once this page has fully finished loading
    window.addEventListener('load', function () {
        loader.style.display = 'none';
    });

    // Show it the instant the user navigates to another page
    window.addEventListener('beforeunload', function () {
        loader.style.display = 'flex';
    });
})();
</script>


<?php echo $__env->make('components.mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<main class="container-fluid" style="margin-top:70px;">
    <?php echo $__env->yieldContent('content'); ?>
</main>


<?php echo $__env->yieldPushContent('scripts'); ?>

<?php echo $__env->make('components.dashboard_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/main.blade.php ENDPATH**/ ?>