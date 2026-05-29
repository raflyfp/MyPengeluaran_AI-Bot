<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MyPengeluaran') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#F7FAFC] font-sans text-[#181C1E] antialiased selection:bg-[#6FD1D7]/30 selection:text-[#093C5D]">
        <main class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_right,rgba(111,209,215,0.28),transparent_34%),linear-gradient(180deg,#F7FAFC_0%,#EAF7F8_100%)] lg:grid lg:grid-cols-[0.95fr_1.05fr]">
            <section class="hidden min-h-screen flex-col justify-between p-8 lg:flex">
                <div class="max-w-md">
                    <div class="inline-flex items-center gap-3 rounded-full border border-white/70 bg-white/70 px-4 py-2 shadow-[0_12px_30px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-[#093C5D] to-[#6FD1D7] text-sm font-extrabold text-white">MP</span>
                        <span class="text-sm font-extrabold tracking-normal text-[#093C5D]">MyPengeluaran</span>
                    </div>

                    <div class="mt-16">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#3B7597]">Smart finance App</p>
                        <h1 class="mt-4 text-5xl font-extrabold leading-tight tracking-normal text-[#093C5D]">Catat uang masuk dan keluar tanpa ribet.</h1>
                        <p class="mt-5 max-w-lg text-base font-medium leading-7 text-[#3C4A42]">Dashboard fintech ringan dengan pencatatan manual, analytics, dan Telegram bot untuk logging transaksi cepat.</p>
                    </div>
                </div>

                {{-- <div class="grid max-w-xl grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-white/70 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Balance</p>
                        <p class="mt-2 text-2xl font-extrabold text-[#093C5D]">Live</p>
                    </div>
                    <div class="rounded-2xl border border-white/70 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Bot</p>
                        <p class="mt-2 text-2xl font-extrabold text-[#007A53]">Telegram</p>
                    </div>
                    <div class="rounded-2xl border border-white/70 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Charts</p>
                        <p class="mt-2 text-2xl font-extrabold text-[#0D5DCF]">Apex</p>
                    </div>
                </div> --}}
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-8">
                <div class="w-full max-w-[430px]">
                    <div class="mb-6 flex items-center justify-between lg:hidden">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-[#093C5D] to-[#6FD1D7] text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(9,60,93,0.16)]">MP</span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">Welcome to</p>
                                <p class="text-lg font-extrabold text-[#093C5D]">MyPengeluaran</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-white/80 bg-white/82 p-5 shadow-[0_24px_60px_rgba(9,60,93,0.13)] backdrop-blur-2xl sm:p-6">
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
