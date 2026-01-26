{{-- <!DOCTYPE html>
<html>
<head>
    <title>Login SCM Ayu Mart</title>
</head>
<body>
    <h2>Login SCM Ayu Mart</h2>

    @if ($errors->any())
        <p style="color:red">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <label>Username</label><br>
        <input type="text" name="username"><br><br>

        <label>Password</label><br>
        <input type="password" name="password"><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html> --}}
@extends('layouts.app_login')
@section('titlePage', 'Login')
@section('app')
    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                    <div class="login-brand">
                        <img src="{{ asset('../assets/image/logo.jpeg') }}" alt="logo" width="100"
                            class="shadow-light rounded-circle">
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h4>Login</h4>
                        </div>
                        @if ($errors->any())
                            <p style="color:red">{{ $errors->first() }}</p>
                        @endif
                        <div class="card-body">
                            <form method="POST" action="{{ route('login.post') }}" class="needs-validation" novalidate="">
                                @csrf
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input id="username" type="text" class="form-control" name="username" tabindex="1"
                                        required autofocus>
                                    <div class="invalid-feedback">
                                        Please fill in your username
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="d-block">
                                        <label for="password" class="control-label">Password</label>
                                    </div>
                                    <input id="password" type="password" class="form-control" name="password"
                                        tabindex="2" required>
                                    <div class="invalid-feedback">
                                        please fill in your password
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                        Login
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="simple-footer" style="color: white;">
                        Copyright &copy; {{ config('app.name') }} 2025
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
