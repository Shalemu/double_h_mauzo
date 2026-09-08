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

        .login-card {
            max-width: 400px;
            width: 100%;
            background: #ffffff;
            border-radius: 10px;
            padding: 36px 40px;
            box-shadow:
                0 30px 70px rgba(0, 0, 0, 0.10),
                0 10px 25px rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(0, 0, 0, 0.04);
            animation: card-in 0.5s ease both;
        }

        @keyframes  card-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h2 {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.2px;
            color: #1f1f1f;
            margin: 0 0 16px;
            text-align: center;
            position: relative;
            padding-bottom: 16px;
        }

        .login-card h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 44px;
            height: 3px;
            border-radius: 3px;
            background: linear-gradient(90deg, #a02128, #d4626a);
        }

        .form-group {
            position: relative;
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #9a9a9a;
            margin-bottom: 7px;
        }

        .form-group i.field-icon {
            position: absolute;
            left: 16px;
            top: 39px;
            color: #c2c2c2;
            font-size: 16px;
        }

        .form-group .toggle-password {
            position: absolute;
            right: 16px;
            top: 39px;
            color: #c2c2c2;
            cursor: pointer;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 44px;
            border: 1.5px solid #ececec;
            border-radius: 10px;
            background: #fafafa;
            transition: 0.2s;
            font-size: 14.5px;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .form-control:focus {
            outline: none;
            border-color: #a02128;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(160, 33, 40, 0.08);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 13px;
        }

        .form-options .remember {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #777;
            cursor: pointer;
        }

        .form-options input[type="checkbox"] {
            accent-color: #a02128;
            width: 15px;
            height: 15px;
        }

        .form-options a {
            color: #a02128;
            text-decoration: none;
            font-weight: 500;
        }

        .form-options a:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 10px;
            background: #a02128;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            letter-spacing: 0.4px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 10px 20px rgba(160, 33, 40, 0.3);
        }

        .btn-login:hover {
            background: #841a20;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(160, 33, 40, 0.38);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-hint {
            text-align: center;
            margin-top: 14px;
            font-size: 13.5px;
            color: #9a9a9a;
        }

        .register-hint a {
            color: #a02128;
            font-weight: 600;
            text-decoration: none;
        }

        .alert-premium {
            background: #fdf1f1;
            border-left: 4px solid #c0392b;
            color: #a8323a;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13.5px;
            margin-bottom: 20px;
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

        @media (max-width: 600px) {
            .login-card { padding: 28px 24px; border-radius: 8px; }
        }
    </style>
</head>
<body>

<div class="login-row">

    <div class="login-brand">
        <div class="login-brand-logo">
            <img src="<?php echo e(asset('assets/img/logo.png')); ?>" alt="Double H Cosmetics">
        </div>
        <h1>Double H Cosmetics</h1>
        <p>Manage inventory, sales and purchases from one admin panel built for your team.</p>
    </div>

    <div class="login-panel">
    <div class="login-card">

        <h2>Welcome Back</h2>

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


            <div class="form-group">
                <label>Email or Username</label>
                <i class="bi bi-person field-icon"></i>
                <input id="validation-email"
                       class="form-control"
                       placeholder="Enter email or username"
                       name="email"
                       type="text"
                       value="<?php echo e(old('email')); ?>"
                       required
                       autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <i class="bi bi-lock field-icon"></i>
                <input id="validation-password"
                       class="form-control"
                       name="password"
                       type="password"
                       placeholder="Enter password"
                       minlength="4"
                       required>
                <i class="bi bi-eye toggle-password" id="togglePassword"></i>
            </div>

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" name="remember" checked> Remember me
                </label>
                <a href="<?php echo e(url('/password/lost')); ?>">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login" name="login" value="login">Sign In</button>

            <div class="register-hint">
                Don't have an account? <a href="<?php echo e(route('register')); ?>">Register here</a>
            </div>
        </form>

    </div>
    </div>

</div>

<script>
    (function () {
        var toggle = document.getElementById('togglePassword');
        var passwordInput = document.getElementById('validation-password');
        if (toggle && passwordInput) {
            toggle.addEventListener('click', function () {
                var isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggle.classList.toggle('bi-eye', !isPassword);
                toggle.classList.toggle('bi-eye-slash', isPassword);
            });
        }
    })();
</script>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/auth/login.blade.php ENDPATH**/ ?>