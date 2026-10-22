<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Sign In - Double H Cosmetics</title>
    <link rel="icon" href="<?php echo e(asset('assets/img/logo.png')); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            color: #4a4a4a;
        }

        .login-row {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Left brand panel — fixed width, full row height */
        .login-brand {
            width: 42%;
            min-width: 380px;
            background: linear-gradient(160deg, #1f1f1f 0%, #2c2c2c 55%, #3a3a3a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            top: -180px;
            right: -160px;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.035);
            bottom: -140px;
            left: -120px;
        }

        .login-brand-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 420px;
            padding: 56px 32px 48px;
            max-width: 380px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            position: relative;
            z-index: 1;
        }

        .login-brand-box p {
            margin: 0;
        }

        .login-brand-logo {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            position: relative;
            z-index: 1;
        }

        .login-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-brand h1 {
            color: #fff;
            font-size: 26px;
            font-weight: 600;
            margin: 28px 0 10px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .login-brand p {
            color: rgba(255, 255, 255, 0.55);
            font-size: 14px;
            text-align: center;
            max-width: 320px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        /* Right form panel — fills remaining row width */
        .login-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 40px 20px;
        }

        /* Login card - AdminLTE (Faramas) style */
        .login-box {
            width: 400px;
            max-width: 100%;
            animation: card-in 0.5s ease both;
        }

        @keyframes  card-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            margin-bottom: 0.9rem;
            font-size: 2.1rem;
            font-weight: 300;
            text-align: center;
            color: #6c757d;
        }

        .login-logo b {
            font-weight: 700;
        }

        .login-card {
            background: #fff;
            border-radius: 0.375rem;
            box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .login-card-body {
            padding: 20px;
            color: #6c757d;
        }

        .login-box-msg {
            padding: 0 20px 20px;
            margin: 0;
            text-align: center;
        }

        .input-group {
            display: flex;
            align-items: stretch;
            margin-bottom: 1rem;
        }

        .input-group .form-control {
            flex: 1 1 auto;
            min-width: 0;
            height: 38px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-right: 0;
            border-radius: 0.375rem 0 0 0.375rem;
            background: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 14.5px;
            color: #212529;
            transition: border-color 0.15s ease-in-out;
        }

        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0 0.375rem 0.375rem 0;
            color: #6c757d;
            background: transparent;
            transition: border-color 0.15s ease-in-out;
        }

        .input-group .form-control:focus {
            outline: none;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: #d0898d;
        }

        .toggle-password {
            cursor: pointer;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #6c757d;
            cursor: pointer;
            font-size: 14px;
        }

        .form-check input[type="checkbox"] {
            accent-color: #a02128;
            width: 15px;
            height: 15px;
        }

        .btn-login {
            min-width: 110px;
            height: 38px;
            padding: 0 16px;
            border: 1px solid #a02128;
            border-radius: 0.375rem;
            background: #a02128;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 14.5px;
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }

        .btn-login:hover {
            background: #841a20;
            border-color: #841a20;
        }

        .login-links {
            margin: 16px 0 0;
            font-size: 14px;
        }

        .login-links p {
            margin: 0 0 4px;
        }

        .login-links a {
            color: #a02128;
            text-decoration: none;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        .alert-premium {
            background: #fdf1f1;
            border-left: 4px solid #c0392b;
            color: #a8323a;
            border-radius: 0.375rem;
            padding: 10px 14px;
            font-size: 13.5px;
            margin-bottom: 16px;
        }

        .alert-premium ul {
            margin: 0;
            padding-left: 18px;
        }

        .alert-premium-success {
            background: #f0f9f0;
            border-left-color: #2e7d32;
            color: #2e7d32;
        }

        @media (max-width: 900px) {
            .login-brand { display: none; }
            .login-panel { padding: 40px 20px; }
        }

        @media (max-width: 576px) {
            .login-box { width: 90%; }
            .login-logo { font-size: 1.75rem; }
        }
    </style>
</head>
<body>

<div class="login-row">

    <div class="login-brand">
        <div class="login-brand-box">
            <div class="login-brand-logo">
                <img src="<?php echo e(asset('assets/img/logo.png')); ?>" alt="Double H Cosmetics">
            </div>
            <h1>Double H Cosmetics</h1>
            <p>Manage inventory, sales and purchases from one admin panel built for your team.</p>
        </div>
    </div>

    <div class="login-panel">
    <div class="login-box">

        <div class="login-logo"><b>MEMBER LOGIN</b></div>

        <div class="login-card">
        <div class="login-card-body">

        <p class="login-box-msg">Sign in to start your session</p>

        <?php if(Auth::check()): ?>
            <script>window.location = "/main/dashboard";</script>
        <?php endif; ?>

        <?php if($message = Session::get('error')): ?>
            <div class="alert-premium"><?php echo e($message); ?></div>
        <?php endif; ?>

        <?php if($message = Session::get('success')): ?>
            <div class="alert-premium alert-premium-success"><?php echo e($message); ?></div>
        <?php endif; ?>

        <?php if(count($errors) > 0): ?>
            <div class="alert-premium">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>">
            <?php echo e(csrf_field()); ?>


            <div class="input-group">
                <input id="validation-email"
                       class="form-control"
                       placeholder="Email or Username"
                       name="email"
                       type="text"
                       value="<?php echo e(old('email')); ?>"
                       required
                       autofocus>
                <div class="input-group-text">
                    <span class="bi bi-envelope"></span>
                </div>
            </div>

            <div class="input-group">
                <input id="validation-password"
                       class="form-control"
                       name="password"
                       type="password"
                       placeholder="Password"
                       minlength="4"
                       required>
                <div class="input-group-text toggle-password" id="togglePassword" title="Show password">
                    <span class="bi bi-lock-fill"></span>
                </div>
            </div>

            <div class="form-row">
                <label class="form-check">
                    <input type="checkbox" name="remember" checked> Remember Me
                </label>
                <button type="submit" class="btn-login" name="login" value="login">Sign In</button>
            </div>
        </form>

        <div class="login-links">
            <p><a href="<?php echo e(url('/password/lost')); ?>">I forgot my password</a></p>
            <p><a href="<?php echo e(route('register')); ?>">Register a new membership</a></p>
        </div>

        </div>
        </div>

    </div>
    </div>

</div>

<script>
    (function () {
        var toggle = document.getElementById('togglePassword');
        var passwordInput = document.getElementById('validation-password');
        if (toggle && passwordInput) {
            var icon = toggle.querySelector('span');
            toggle.addEventListener('click', function () {
                var isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                icon.classList.toggle('bi-lock-fill', !isPassword);
                icon.classList.toggle('bi-unlock-fill', isPassword);
                toggle.title = isPassword ? 'Hide password' : 'Show password';
            });
        }
    })();
</script>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/auth/login.blade.php ENDPATH**/ ?>