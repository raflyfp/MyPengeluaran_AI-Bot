<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'MyPengeluaran') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F7FAFC] font-sans text-[#181C1E] antialiased selection:bg-[#6FD1D7]/30 selection:text-[#093C5D]">
        <div class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_top_right,rgba(111,209,215,0.24),transparent_32%),linear-gradient(180deg,#F7FAFC_0%,#EEF7FA_100%)]">
            {{ $slot }}

            @auth
                <x-add-transaction-modal />
            @endauth
        </div>
    </body>
</html>
