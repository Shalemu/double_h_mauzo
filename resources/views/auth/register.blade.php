@extends('layouts.app')

@section('content')

<style>
@font-face {
    font-family: "Montserrat-Regular";
    src: url("{{ asset('fonts/montserrat/Montserrat-Regular.ttf') }}");
}
@font-face {
    font-family: "Montserrat-Medium";
    src: url("{{ asset('fonts/montserrat/Montserrat-Medium.ttf') }}");
}
@font-face {
    font-family: "ElMessiri-SemiBold";
    src: url("{{ asset('fonts/el_messiri/ElMessiri-SemiBold.ttf') }}");
}

* { box-sizing: border-box; }

body {
    font-family: "Montserrat-Regular";
    font-size: 15px;
    color: #666;
}

.wrapper {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: #f5f7fb;
}

.inner {
    max-width: 900px;
    width: 100%;
    margin: auto;
    display: flex;
    background: #fff;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.image-holder {
    width: 45%;
}

.image-holder img {
    width: 100%;
    border-radius: 15px;
}

form {
    width: 55%;
    padding-left: 30px;
}

h3 {
    font-family: "ElMessiri-SemiBold";
    font-size: 26px;
    text-align: center;
    margin-bottom: 25px;
    text-transform: uppercase;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 18px;
}

.form-group {
    position: relative;
    width: 100%;
}

.form-group i {
    position: absolute;
    top: 50%;
    left: 15px;
    transform: translateY(-50%);
    color: #999;
}

.form-group .toggle-password {
    left: auto;
    right: 15px;
    cursor: pointer;
}

.form-control {
    width: 100%;
    height: 47px;
    padding: 0 45px;
    border: 1px solid #e6e6e6;
    border-radius: 6px;
    transition: 0.3s;
}

.form-control:focus {
    border-color: #84cde1;
}

button {
    width: 100%;
    height: 48px;
    background: #84cde1;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 25px;
    font-family: "Montserrat-Medium";
    text-transform: uppercase;
    transition: 0.3s;
}

button:hover {
    background: #17c8f8;
}

.login-link {
    text-align: center;
    margin-top: 15px;
}

.login-link a {
    color: #17c8f8;
    font-weight: 500;
}

@media (max-width: 768px) {
    .inner {
        flex-direction: column;
        padding: 25px;
    }

    .image-holder {
        display: none;
    }

    form {
        width: 100%;
        padding-left: 0;
    }

    .form-row {
        flex-direction: column;
    }
}
</style>

<div class="wrapper">
    <div class="inner">

        <div class="image-holder">
            <img src="{{ asset('assets/img/registration-form-61.jpg') }}" alt="Register">
        </div>

     <form method="POST" action="{{ route('register') }}">
    @csrf

    <h3>Create Account</h3>

    <div class="form-row">
        <div class="form-group">
            <i class="zmdi zmdi-account"></i>
            <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
        </div>

        <div class="form-group">
            <i class="zmdi zmdi-account"></i>
            <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <i class="zmdi zmdi-account"></i>
            <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
        </div>

        <div class="form-group">
            <i class="zmdi zmdi-phone"></i>
            <input type="text" name="phone" class="form-control" placeholder="Phone" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <i class="zmdi zmdi-email"></i>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
    </div>


    <div class="form-row">
        <div class="form-group">
            <i class="zmdi zmdi-lock"></i>
            <input type="password" name="password" class="form-control password" placeholder="Password" required>
            <i class="zmdi zmdi-eye toggle-password"></i>
        </div>

        <div class="form-group">
            <i class="zmdi zmdi-lock"></i>
            <input type="password" name="password_confirmation" class="form-control password" placeholder="Confirm Password" required>
            <i class="zmdi zmdi-eye toggle-password"></i>
        </div>
    </div>

    <button type="submit">
        Register <i class="zmdi zmdi-long-arrow-right"></i>
    </button>

    <div class="login-link">
        Already have an account?
        <a href="{{ route('login') }}">Login</a>
    </div>
</form>

    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(icon => {
    icon.addEventListener('click', function () {
        const input = this.previousElementSibling;
        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('zmdi-eye');
            this.classList.add('zmdi-eye-off');
        } else {
            input.type = 'password';
            this.classList.remove('zmdi-eye-off');
            this.classList.add('zmdi-eye');
        }
    });
});
</script>

@endsection
