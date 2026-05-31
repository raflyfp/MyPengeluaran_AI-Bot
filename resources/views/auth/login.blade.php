<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#3B7597]">Secure access</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-normal text-[#093C5D]">Login</h1>
        <p class="mt-2 text-sm font-medium leading-6 text-[#72777E]">Masuk untuk melihat dashboard keuangan dan transaksi terbaru kamu.</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-2xl bg-[#DFF8F4] px-4 py-3 text-sm font-bold text-[#007A53] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(9,60,93,0.12)]" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <label for="email" class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Email</span>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0"
            >
        </label>
        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <label for="password" class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Password</span>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Enter password"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0"
            >
        </label>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-[#B8C7CC] text-[#007A53] shadow-sm focus:ring-[#6FD1D7]" name="remember">
                <span class="text-sm font-semibold text-[#3C4A42]">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-bold text-[#007A53] transition hover:text-[#093C5D]" href="{{ route('password.request') }}">
                    Forgot?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center rounded-full bg-[#093C5D] px-5 py-3.5 text-base font-extrabold text-white shadow-[0_16px_32px_rgba(9,60,93,0.24)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]"
        >
            Login
        </button>
    </form>

    <div class="mt-5 rounded-2xl border border-[#D9E8ED] bg-white/70 p-4 text-center transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(9,60,93,0.1)]">
        <p class="text-sm font-semibold text-[#72777E]">Belum punya akun?</p>
        <a
            href="{{ route('register') }}"
            class="mt-3 flex w-full items-center justify-center rounded-full border border-[#BFE4E8] bg-[#EAF7F8] px-5 py-3 text-sm font-extrabold text-[#093C5D] transition duration-200 hover:bg-white active:scale-[0.98]"
        >
            Create Account
        </a>
    </div>
</x-guest-layout>
