@extends('layouts/blankLayout')

@section('title', 'Forgot Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Centered White Card -->
            <div class="card px-sm-6 px-0 shadow-3d-light border-light-silver">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                        </a>
                    </div>
                    <!-- /Logo -->



                    <form id="formAuthentication" class="mb-6" action="{{ url('/') }}" method="GET">
                        <div class="mb-6">
                            <label for="email" class="form-label text-charcoal">Email address</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="name@anhet.com" autofocus required />
                        </div>
                        
                        <div class="mb-6">
                            <button class="btn btn-primary d-grid w-100 py-3 fw-semibold">Send Reset Link</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" class="forgot-link d-inline-flex align-items-center justify-content-center">
                            <i class="icon-base bx bx-chevron-left me-1"></i>
                            Back to login
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Centered White Card -->
        </div>
    </div>
</div>
@endsection
