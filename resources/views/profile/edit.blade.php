<x-app-layout>
    <div
        class="mx-auto min-h-screen max-w-[430px] pb-32 transition duration-300 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12"
        :class="darkMode ? 'text-white' : ''"
    >
        <!-- Top Fixed Header -->
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('profile.index') }}"
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#093C5D] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                        aria-label="Back to profile"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">MyPengeluaran</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Personal Info</h1>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="grid gap-7 px-5 pt-28 lg:items-start lg:gap-x-8 lg:gap-y-7 lg:max-w-7xl lg:grid-cols-2 lg:px-6 lg:pt-36">
            @if (session('status') === 'profile-updated')
                <div class="rounded-2xl border border-[#BFEDE5] bg-[#DFF8F4] px-4 py-3 text-sm font-bold text-[#007A53] shadow-[0_10px_24px_rgba(9,60,93,0.06)] lg:col-span-2">
                    Profile information updated successfully.
                </div>
            @endif

            @if (session('status') === 'password-updated')
                <div class="rounded-2xl border border-[#BFEDE5] bg-[#DFF8F4] px-4 py-3 text-sm font-bold text-[#007A53] shadow-[0_10px_24px_rgba(9,60,93,0.06)] lg:col-span-2">
                    Password updated successfully.
                </div>
            @endif

            <!-- Profile Info Form -->
            <div class="rounded-2xl border border-white/80 bg-white/76 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Update Password Form -->
            <div class="rounded-2xl border border-white/80 bg-white/76 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:p-6">
                @include('profile.partials.update-password-form')
            </div>

            <!-- Delete Account Form -->
            <div class="rounded-2xl border border-white/80 bg-white/76 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-span-2 lg:p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </main>
    </div>

    <x-bottom-nav active="profile" />
</x-app-layout>
