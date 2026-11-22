<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="_token" content="{{ csrf_token() }}"><title>File Management System</title>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('components/lobibox/dist/css/lobibox.min.css') }}">
@stack('plugin-styles')
<link rel="stylesheet" href="{{ asset('css/file-management.css') }}">
@stack('style')</head>
<body class="{{ ($darkmode ?? 0) ? 'fm-dark' : '' }}">
<script src="{{ asset('components/jquery/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('components/lobibox/dist/js/notifications.min.js') }}"></script>
<div class="fm-shell">@include('layout.sidebar')<div class="fm-workspace">@include('layout.header')<main class="fm-content">@include('flash.message')@yield('content')</main>@include('layout.footer')</div></div>
<script src="{{ asset('assets/plugins/feather-icons/feather.min.js') }}"></script>
@stack('plugin-scripts')
<script src="{{ asset('js/file-management.js') }}"></script>
@stack('custom-scripts')</body></html>