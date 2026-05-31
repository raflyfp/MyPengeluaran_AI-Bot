<x-guest-layout>
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#3B7597]">Create profile</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-normal text-[#093C5D]">Register</h1>
        <p class="mt-2 text-sm font-medium leading-6 text-[#72777E]">Buat akun untuk mulai mencatat pemasukan, pengeluaran, dan Telegram bot activity.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <label for="name" class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Name</span>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Your name"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0"
            >
        </label>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />

        <label for="email" class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Email</span>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
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
                autocomplete="new-password"
                placeholder="Minimum 8 characters"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0"
            >
        </label>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />

        <label for="password_confirmation" class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Confirm Password</span>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Repeat password"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0"
            >
        </label>
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

        <button
            type="submit"
            class="flex w-full items-center justify-center rounded-full bg-[#093C5D] px-5 py-3.5 text-base font-extrabold text-white shadow-[0_16px_32px_rgba(9,60,93,0.24)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]"
        >
            Create Account
        </button>
    </form>

    <div class="mt-5 rounded-2xl border border-[#D9E8ED] bg-white/70 p-4 text-center transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(9,60,93,0.1)]">
        <p class="text-sm font-semibold text-[#72777E]">Sudah punya akun?</p>
        <a
            href="{{ route('login') }}"
            class="mt-3 flex w-full items-center justify-center rounded-full border border-[#BFE4E8] bg-[#EAF7F8] px-5 py-3 text-sm font-extrabold text-[#093C5D] transition duration-200 hover:bg-white active:scale-[0.98]"
        >
            Back to Login
        </a>
    </div>
</x-guest-layout>
