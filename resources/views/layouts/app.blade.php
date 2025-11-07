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
</head>

<body class="font-sans antialiased overflow-hidden">
    <div class="h-screen flex bg-gray-100">
        {{-- Sidebar (navigation) --}}
        @if (!in_array(Route::currentRouteName(), ['fets.select.embed', 'fets.submitted.embed']))
            @include('layouts.navigation')
        @endif

        {{-- Main Section --}}
        <div class="flex-1 flex flex-col h-full">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow flex-shrink-0">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
