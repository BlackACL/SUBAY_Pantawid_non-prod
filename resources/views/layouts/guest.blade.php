<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- <script src="https://www.google.com/recaptcha/api.js"></script> -->
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-cover bg-center" style="background: url('/images/bg.png') no-repeat center center fixed; background-size: cover;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="flex items-center justify-center space-x-4">
                <img src="{{ asset('images/dswd_logo.png') }}" alt="DSWD Logo" style="height: 60px; width: auto;">
                <img src="{{ asset('images/pantawid_logo.png') }}" alt="Pantawid Logo" style="height: 40px; width: auto;">
            </div>

            <div class="flex justify-center mb-5">
                <img src="{{ asset('images/subay_white.png') }}" alt="White Logo" style="height: 100px; width: auto;">
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
