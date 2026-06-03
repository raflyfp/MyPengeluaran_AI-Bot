<section>
    <header class="mb-6">
        <h2 class="text-xl font-extrabold tracking-normal text-[#181C1E]">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm font-semibold text-[#72777E]">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <label for="name"
            class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Name</span>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus
                autocomplete="name" placeholder="Your name"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('name')" />

        <label for="email"
            class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Email</span>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                autocomplete="username" placeholder="you@example.com"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="rounded-2xl bg-[#FFF1D9] p-4 text-[#9A5C00] border border-[#FFF1D9] text-sm">
                <p class="font-semibold">
                    {{ __('Your email address is unverified.') }}
                </p>

                <button form="send-verification" class="mt-2 text-xs font-extrabold underline hover:text-[#093C5D] focus:outline-none">
                    {{ __('Click here to re-send the verification email.') }}
                </button>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-bold text-xs text-[#007A53]">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#093C5D] px-6 py-3 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]">
                {{ __('Save') }}
            </button>
        </div>
    </form>
</section>
