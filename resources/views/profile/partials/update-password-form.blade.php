<section>
    <header class="mb-6">
        <h2 class="text-xl font-extrabold tracking-normal text-[#181C1E]">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm font-semibold text-[#72777E]">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <label for="update_password_current_password"
            class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Current Password</span>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
        </label>
        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />

        <label for="update_password_password"
            class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">New Password</span>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" placeholder="••••••••"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
        </label>
        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

        <label for="update_password_password_confirmation"
            class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Confirm Password</span>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••"
                class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
        </label>
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />

        <div class="flex items-center gap-4 pt-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#093C5D] px-6 py-3 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]">
                {{ __('Save') }}
            </button>
        </div>
    </form>
</section>
