<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/dswd_logo_hand.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Temporarily disabled for testing --}}
        {{-- <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script> --}}
        
        <!-- Debug script -->
        <script>
            console.log('Page loaded successfully');
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM loaded, page should be interactive');
            });
        </script>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-cover bg-center min-h-screen" 
        style="background-image: url('/images/bg.png'); background-repeat: no-repeat; background-size: cover;">
        <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">

            <!-- Logos -->
            <div class="flex items-center justify-center space-x-4">
                <img src="{{ asset('images/dswd_logo_white.png') }}" 
                    alt="DSWD Logo" 
                    class="h-12 sm:h-16 md:h-20 lg:h-20 w-auto">
                <img src="{{ asset('images/pantawid_logo_white.png') }}" 
                    alt="Pantawid Logo" 
                    class="h-12 sm:h-16 md:h-20 lg:h-20 w-auto">
            </div>

            <!-- Card -->
            @if (!request()->routeIs('verify') && !request()->routeIs('verify.process'))
                <div class="{{ $cardClass ?? 'w-full sm:max-w-md md:max-w-lg lg:max-w-md xl:max-w-lg 2xl:max-w-lg' }} mt-6 px-6 bg-white shadow-md rounded-lg">
                    {{ $slot }}
                </div>
            @else
                <!-- On Two-Factor routes, render slot directly (two-factor-challenge.blade.php will handle its own card) -->
                {{ $slot }}
            @endif
        </div>
        @stack('scripts')
    </body>
</html>
