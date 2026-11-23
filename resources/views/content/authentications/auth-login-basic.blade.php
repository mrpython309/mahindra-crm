@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Centered White Login Card -->
            <div class="card px-sm-6 px-0 shadow-3d-light border-light-silver">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                        </a>
                    </div>
                    <!-- /Logo -->



                    <form id="formAuthentication" class="mb-6" action="{{ route('login.submit') }}" method="POST">
                       @csrf
                        <div class="mb-6">
                            <label for="email" class="form-label text-charcoal">Email address</label>
                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="name@anhet.com" autofocus required />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label text-charcoal" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-8">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                                    <label class="form-check-label text-muted" for="remember-me"> Remember Me </label>
                                </div>
                                <a href="{{ route('auth-reset-password-basic') }}" class="forgot-link">
                                    Forgot Password?
                                </a>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <button class="btn btn-primary d-grid w-100 py-3 fw-semibold" type="submit">Sign In</button>
                        </div>
                    </form>

                    @if ($errors->any())
                        <div class="alert alert-danger-light d-flex align-items-center p-3 mb-0" role="alert">
                            <i class="bx bx-error-circle me-2 fs-5"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif
                </div>
            </div>
            <!-- /Centered White Login Card -->
        </div>
    </div>
</div>
@endsection
