@extends('layout.master2')
@section('content')
<section class="fm-login-card">
@if(!empty($config->login_logo))<img class="fm-login-logo" src="{{ asset('dist/img/'.$config->login_logo) }}" alt="File Management System">@else<h1>File Management System</h1>@endif
@if($errors->any())<div class="alert alert-danger" role="alert" data-fm-lockout-seconds="{{ session('lockout_seconds', 0) }}">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<label class="sr-only" for="username">Username</label><div class="input-group mb-4"><div class="input-group-prepend"><span class="input-group-text"><i data-feather="user"></i></span></div><input class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" autofocus required></div>
<label class="sr-only" for="password">Password</label><div class="input-group mb-4"><div class="input-group-prepend"><span class="input-group-text"><i data-feather="key"></i></span></div><input class="form-control" id="password" name="password" type="password" placeholder="Password" autocomplete="current-password" required><div class="input-group-append"><button class="btn fm-password-button" type="button" id="fm-password-toggle" aria-label="Show password"><i data-feather="eye"></i></button></div></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="remember" name="remember"><label class="form-check-label" for="remember">Remember me</label></div><div class="text-center"><button class="btn btn-primary" type="submit">Login</button></div></form></section>
@endsection