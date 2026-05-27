<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    @includeIf('frontend.layout.seo')
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset($themeOptions['general']['favicon_icon'] ?? 'admin/images/faviconIcon.png') }}" type="image/x-icon">

    <link rel="shortcut icon" href="{{ asset($themeOptions['general']['favicon_icon'] ?? 'admin/images/faviconIcon.png') }}" type="image/x-icon">
    <title>@yield('title')</title>

    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/bootstrap.css') }}">

    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Golos+Text:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/toastr.min.css') }}">

    <!-- ICONSAX css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/iconsax.css') }}">

    <!-- Feather icon css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/feather-icon/feather-icon.css') }}">

    @vite(['public/admin/scss/admin.scss', 'resources/js/app.js'])
    <style>
        :root {
            --bs-body-font-family: "Poppins", "Golos Text", sans-serif;
            --bs-font-sans-serif: "Poppins", "Golos Text", sans-serif;
            --bs-btn-font-family: "Poppins", "Golos Text", sans-serif;
        }
        body, p, h1, h2, h3, h4, h5, h6, a, span,
        input, textarea, select, button, label,
        td, th, li, .form-control, .form-select, .btn {
            font-family: "Poppins", "Golos Text", sans-serif !important;
        }
        ::placeholder { font-family: "Poppins", "Golos Text", sans-serif !important; }
    </style>
</head>

<body onload="startTime()">

    <div class="page-wrapper">
        @yield('content')
    </div>

    <!-- Latest jquery -->
    <script src="{{ asset('admin/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-ui.min.js') }}"></script>

    <script src="{{ asset('admin/js/toastr.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('admin/js/bootstrap.min.js') }}"></script>

    <!-- Feather icon js -->
    <script src="{{ asset('admin/js/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin/js/feather-icon/feather-icon.js') }}"></script>

    <!-- Iconsax js -->
    <script src="{{ asset('admin/js/iconsax.js') }}"></script>
    <!-- Clock js -->
    <script src="{{ asset('admin/js/clock.js') }}"></script>
    <!-- Time js -->
    <script src="{{ asset('admin/js/time.js') }}"></script>
    <!-- Password hide show js -->

     <!-- Tinymce Editor -->
     <script src="{{ asset('admin/js/tinymce/tinymce.js') }}"></script>


    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.min.js') }}"></script>

    <script src="{{ asset('admin/js/select2.full.min.js') }}"></script>
    @stack('js')

    @include('backend.layouts.partials.script')
</body>

</html>
