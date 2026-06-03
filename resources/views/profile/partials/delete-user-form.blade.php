<section class="space-y-6">
    <header class="mb-6">
        <h2 class="text-xl font-extrabold tracking-normal text-[#BA1A1A]">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm font-semibold text-[#72777E]">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center justify-center rounded-full border border-[#FFD3D3] bg-white px-6 py-3 text-sm font-extrabold text-[#BA1A1A] shadow-[0_14px_28px_rgba(186,26,26,0.05)] transition duration-200 hover:bg-[#FFF5F5] active:scale-[0.98]"
    >
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-[#181C1E]">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-[#72777E]">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <label for="password"
                    class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-4 transition duration-200 hover:shadow-[0_12px_24px_rgba(9,60,93,0.08)] focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
                    <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Password</span>
                    <input id="password" name="password" type="password" placeholder="••••••••" required
                        class="mt-2 w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#9AA4AA] focus:ring-0">
                </label>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="rounded-full border border-[#DCE8EB] bg-white px-5 py-3 text-sm font-extrabold text-[#093C5D] transition hover:bg-[#EEF4F7] active:scale-95"
                >
                    {{ __('Cancel') }}
                </button>

                <button
                    type="submit"
                    class="rounded-full bg-[#BA1A1A] px-5 py-3 text-sm font-extrabold text-white shadow-md transition hover:bg-[#A11616] active:scale-95"
                >
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
