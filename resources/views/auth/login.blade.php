<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - Double H Cosmetics</title>
    <link rel="icon" href="{{ asset('assets/img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            color: #4a4a4a;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #ffffff;
        }

        .login-card {
            max-width: 560px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 48px;
            box-shadow:
                0 30px 70px rgba(0, 0, 0, 0.10),
                0 10px 25px rgba(0, 0, 0, 0.06),
                0 0 0 1px rgba(0, 0, 0, 0.03);
            animation: card-in 0.5s ease both;
        }

        @keyframes card-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 8px;
        }

        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-card h2 {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            color: #262626;
            margin: 0 0 4px;
        }

        .login-card .sub {
            text-align: center;
            color: #9a9a9a;
            font-size: 13.5px;
            margin-bottom: 16px;
        }

        .form-group {
            position: relative;
            margin-bottom: 12px;
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
            left: 15px;
            top: 34px;
            color: #c2c2c2;
            font-size: 16px;
        }

        .form-group .toggle-password {
            position: absolute;
            right: 15px;
            top: 34px;
            color: #c2c2c2;
            cursor: pointer;
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            height: 40px;
            padding: 0 42px;
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
            border-color: #262626;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05);
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
            accent-color: #262626;
            width: 15px;
            height: 15px;
        }

        .form-options a {
            color: #262626;
            text-decoration: none;
            font-weight: 500;
        }

        .form-options a:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 42px;
            border: none;
            border-radius: 10px;
            background: #1f1f1f;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            letter-spacing: 0.4px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.25s;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-login:hover {
            background: #000;
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(0, 0, 0, 0.2);
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
            color: #262626;
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

        @media (max-width: 600px) {
            .login-card { padding: 24px 26px; border-radius: 16px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Double H Cosmetics">
        </div>

        <h2>Welcome Back</h2>
        <p class="sub">Sign in to Double H Cosmetics Admin Panel</p>

        @if(Auth::check())
            <script>window.location = "/main/dashboard";</script>
        @endif

        @if($message = Session::get('error'))
            <div class="alert-premium">{{ $message }}</div>
        @endif

        @if (count($errors) > 0)
            <div class="alert-premium">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}

            <div class="form-group">
                <label>Email or Username</label>
                <i class="bi bi-person field-icon"></i>
                <input id="validation-email"
                       class="form-control"
                       placeholder="Enter email or username"
                       name="email"
                       type="text"
                       value="{{ old('email') }}"
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
                <a href="{{ url('/password/lost') }}">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login" name="login" value="login">Sign In</button>

            <div class="register-hint">
                Don't have an account? <a href="{{ route('register') }}">Register here</a>
            </div>
        </form>

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
