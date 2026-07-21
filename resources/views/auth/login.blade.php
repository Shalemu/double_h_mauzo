@extends('layouts.app')

@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap');

/* Hide the shared layout's top nav on the login screen only */
.navbar { display: none !important; }

* { box-sizing: border-box; }

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
    background: linear-gradient(135deg, #f7ecec 0%, #f3e9de 50%, #f8f1e7 100%);
}

.login-card {
    max-width: 950px;
    width: 100%;
    display: flex;
    background: #fff;
    border-radius: 24px;
    overflow: hidden;
    box-shadow:
        0 45px 90px rgba(40, 8, 14, 0.35),
        0 15px 35px rgba(40, 8, 14, 0.22),
        0 0 0 1px rgba(122, 31, 47, 0.05);
}

/* Branding panel */
.login-brand {
    width: 44%;
    position: relative;
    padding: 55px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: linear-gradient(160deg, #7a1f2f 0%, #4a1119 60%, #1c0a0d 100%);
    color: #f5e9df;
    overflow: hidden;
}

.login-brand::before,
.login-brand::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.login-brand::before {
    width: 260px;
    height: 260px;
    top: -90px;
    right: -90px;
    background: radial-gradient(circle, rgba(212,175,55,0.35), transparent 70%);
}

.login-brand::after {
    width: 200px;
    height: 200px;
    bottom: -70px;
    left: -60px;
    background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 70%);
}

.brand-logo-badge {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    position: relative;
    z-index: 1;
    padding: 10px;
}

.brand-logo-badge img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.login-brand h1 {
    font-family: 'Playfair Display', serif;
    font-size: 27px;
    letter-spacing: 0.5px;
    margin: 26px 0 6px;
    position: relative;
    z-index: 1;
}

.login-brand .tagline {
    font-size: 12.5px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #d4af37;
    position: relative;
    z-index: 1;
}

.login-brand p.desc {
    margin-top: 18px;
    margin-bottom: 0;
    font-size: 13.5px;
    line-height: 1.7;
    color: rgba(245,233,223,0.75);
    position: relative;
    z-index: 1;
}

.brand-features {
    position: relative;
    z-index: 1;
    margin: 30px 0 0;
    padding: 0;
    list-style: none;
}

.brand-features li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    margin-bottom: 12px;
    color: rgba(245,233,223,0.85);
}

.brand-features i {
    color: #d4af37;
    font-size: 16px;
}

.brand-footer {
    position: relative;
    z-index: 1;
    font-size: 11px;
    letter-spacing: 0.5px;
    color: rgba(245,233,223,0.5);
}

/* Form panel */
.login-form-panel {
    width: 56%;
    padding: 55px;
}

.login-form-panel h2 {
    font-family: 'Playfair Display', serif;
    font-size: 27px;
    color: #2b2b2b;
    margin-bottom: 6px;
}

.login-form-panel .sub {
    color: #999;
    font-size: 13.5px;
    margin-bottom: 28px;
}

.form-group {
    position: relative;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #8a8a8a;
    margin-bottom: 7px;
}

.form-group i.field-icon {
    position: absolute;
    left: 15px;
    top: 40px;
    color: #b98a8f;
    font-size: 17px;
}

.form-group .toggle-password {
    position: absolute;
    right: 15px;
    top: 40px;
    color: #b0b0b0;
    cursor: pointer;
    font-size: 17px;
}

.form-control {
    width: 100%;
    height: 48px;
    padding: 0 42px;
    border: 1.5px solid #ececec;
    border-radius: 10px;
    background: #fbfaf9;
    transition: 0.25s;
    font-size: 14.5px;
}

.form-control:focus {
    outline: none;
    border-color: #7a1f2f;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(122,31,47,0.08);
}

.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
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
    accent-color: #7a1f2f;
    width: 15px;
    height: 15px;
}

.form-options a {
    color: #7a1f2f;
    text-decoration: none;
    font-weight: 500;
}

.form-options a:hover {
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    height: 50px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #7a1f2f, #4a1119);
    color: #f5e9df;
    font-weight: 600;
    letter-spacing: 0.5px;
    font-size: 14px;
    text-transform: uppercase;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: 0 12px 24px rgba(122,31,47,0.28);
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 16px 30px rgba(122,31,47,0.36);
}

.register-hint {
    text-align: center;
    margin-top: 26px;
    font-size: 13.5px;
    color: #999;
}

.register-hint a {
    color: #7a1f2f;
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

@media (max-width: 860px) {
    .login-card { flex-direction: column; border-radius: 18px; }
    .login-brand, .login-form-panel { width: 100%; }
    .login-brand { padding: 35px 30px; }
    .login-form-panel { padding: 35px 30px; }
}
</style>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-brand">
            <div>
                <div class="brand-logo-badge">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Double H Cosmetics">
                </div>
                <h1>Double H Cosmetics</h1>
                <div class="tagline">Admin Portal</div>
                <p class="desc">Manage products, sales, purchases and staff across every shop from one elegant dashboard.</p>
            </div>

            <ul class="brand-features">
                <li><i class="zmdi zmdi-check-circle"></i> Real-time sales tracking</li>
                <li><i class="zmdi zmdi-check-circle"></i> Multi-shop inventory control</li>
                <li><i class="zmdi zmdi-check-circle"></i> Secure staff &amp; admin access</li>
            </ul>

            <div class="brand-footer">&copy; {{ date('Y') }} Double H Cosmetics. All rights reserved.</div>
        </div>

        <div class="login-form-panel">
            <h2>Welcome Back</h2>
            <p class="sub">Please sign in to continue to your dashboard.</p>

            @if(isset(Auth::user()->email))
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
                    <i class="zmdi zmdi-account field-icon"></i>
                    <input id="validation-email"
                           class="form-control"
                           placeholder="Enter email or username"
                           name="email"
                           type="text"
                           value="{{ old('email') }}"
                           data-validation="[NOTEMPTY]">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <i class="zmdi zmdi-lock field-icon"></i>
                    <input id="validation-password"
                           class="form-control"
                           name="password"
                           type="password"
                           placeholder="Enter password"
                           data-validation="[L>=6]"
                           data-validation-message="$ must be at least 6 characters">
                    <i class="zmdi zmdi-eye toggle-password" id="togglePassword"></i>
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
</div>

<script>
    (function () {
        var toggle = document.getElementById('togglePassword');
        var passwordInput = document.getElementById('validation-password');
        if (toggle && passwordInput) {
            toggle.addEventListener('click', function () {
                var isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.classList.toggle('zmdi-eye', !isPassword);
                this.classList.toggle('zmdi-eye-off', isPassword);
            });
        }
    })();
</script>

@endsection
