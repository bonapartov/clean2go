@use('app\Helpers\Helpers')

@php
    $settings = Helpers::getSettings();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('dir', 'ltr') }}">

<head>
    @use('App\Models\Setting')
    @php
        $settings = Setting::first()->values;
    @endphp
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset($settings['general']['favicon']) ?? asset('admin/images/faviconIcon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset($settings['general']['favicon']) ?? asset('admin/images/faviconIcon.png') }}" type="image/x-icon">
    <title>@yield('title') - @isset($settings['general']['site_name'])
            {{ $settings['general']['site_name'] }}
        @endisset {{ __('static.admin_panel') }}</title>

    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,600;1,700;1,800;1,900&family=DM+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Golos+Text:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/bootstrap/bootstrap.min.css') }}">
    <!-- Remix Icon js -->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/remix-icon.css') }}">
    <!-- Datatable css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/datatables.css') }}">
    <!-- Select2 css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/dropzone.css') }}">
    <!-- ICONSAX css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/iconsax.css') }}">

    <!-- Feather icon css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/feather-icon/feather-icon.css') }}">
    @stack('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/toastr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/vendors/select-datatables.min.css') }}">
    <!-- Admin css-->
    @vite(['public/admin/scss/admin.scss', 'resources/js/app.js'])

    <script src="{{ asset('admin/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('admin/js/admin-cart.min.js') }}"></script>
    <script src="{{ asset('admin/js/toastr.min.js') }}"></script>
    @include('inc.style')
    <style>
        :root {
            --bs-body-font-family: "DM Sans", "Golos Text", sans-serif;
            --bs-font-sans-serif: "DM Sans", "Golos Text", sans-serif;
            --bs-btn-font-family: "DM Sans", "Golos Text", sans-serif;
        }
        body, p, h1, h2, h3, h4, h5, h6, a, span,
        input, textarea, select, button, label,
        td, th, li, .form-control, .form-select,
        .btn, .badge, .nav-link, .dropdown-item,
        .select2-selection, .select2-search__field,
        .select2-results__option, .datatable td, .datatable th {
            font-family: "DM Sans", "Golos Text", sans-serif !important;
        }
        ::placeholder { font-family: "DM Sans", "Golos Text", sans-serif !important; }
        ::-webkit-input-placeholder { font-family: "DM Sans", "Golos Text", sans-serif !important; }
        ::-moz-placeholder { font-family: "DM Sans", "Golos Text", sans-serif !important; }
    </style>
    <script>
        const baseurl = "{{ asset('') }}";
    </script>
</head>

<body class="theme {{ session('dir', 'ltr') }} {{ session('theme', '') }}">

    <div class="page-wrapper">

        @includeIf('backend.layouts.partials.header')

        <div class="page-body-wrapper">

            @includeIf('backend.layouts.partials.sidebar')

            <div class="page-body">

                @includeIf('backend.layouts.partials.breadcrumb')

                <div class="container-fluid">

                    @include('backend.inc.alerts')

                    @yield('content')

                </div>

            </div>

            @includeIf('backend.layouts.partials.footer')

            @include('backend.inc.modal')

        </div>

    </div>

    <!-- Dark mode js -->
    <script src="{{ asset('admin/js/dark-mode.js') }}"></script>
    
    <!-- Bootstrap js -->
    <script src="{{ asset('admin/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/js/bootstrap/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('admin/js/bootstrap/popper.min.js') }}"></script>

    <!-- Feather icon js -->
    <script src="{{ asset('admin/js/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('admin/js/feather-icon/feather-icon.js') }}"></script>

    <!-- Iconsax js -->
    <script src="{{ asset('admin/js/iconsax.js') }}"></script>

    <!-- Height equal js -->
    <script src="{{ asset('admin/js/height-equal.js') }}"></script>

    <!-- Sidebar jquery -->
    <script src="{{ asset('admin/js/sidebar-menu.js') }}"></script>

    <!-- Tooltip js -->
    <script src="{{ asset('admin/js/tooltip-init.js') }}"></script>

    <!-- Tinymce Editor -->
    <script src="{{ asset('admin/js/tinymce/tinymce.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('admin/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/js/dropzone.js') }}"></script>

    <script src="{{ asset('admin/js/datatables.min.js') }}"></script>
    <script src="{{ asset('admin/js/buttons.server-side.js') }}"></script>

    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.js') }}"></script>
    <script src="{{ asset('admin/js/jquery-validation/additional-methods.min.js') }}"></script>

    @stack('js')

    <script src="{{ asset('admin/js/dropzone.js') }}"></script>

    <script src="{{ asset('admin/js/admin-script.js') }}"></script>

    @include('backend.layouts.partials.script')
</body>
@yield('modal')

</html>
