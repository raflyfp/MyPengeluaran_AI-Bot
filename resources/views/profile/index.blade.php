<x-app-layout>
    @php
        $notificationSettings = [
            [
                'key' => 'spendingAlerts',
                'title' => 'Spending alerts',
                'caption' => 'Notify when a category crosses the safe limit.',
                'enabled' => true,
            ],
            [
                'key' => 'weeklySummary',
                'title' => 'Weekly summary',
                'caption' => 'Receive a Sunday night financial recap.',
                'enabled' => true,
            ],
            [
                'key' => 'botReceipts',
                'title' => 'Bot receipt updates',
                'caption' => 'Confirm every parsed receipt from chat bots.',
                'enabled' => false,
            ],
        ];

        $accountSettings = [
            ['label' => 'Personal information', 'caption' => 'Name and email address', 'href' => route('profile.edit')],
            ['label' => 'Security & PIN', 'caption' => 'Password, passcode, device access'],
            ['label' => 'Budget preferences', 'caption' => 'Monthly limits and category goals'],
            ['label' => 'Currency & language', 'caption' => 'Rupiah, English interface'],
        ];

        $displayName = $user?->name ?? auth()->user()?->name ?? 'User';
        $email = $user?->email ?? auth()->user()?->email ?? 'user@example.com';
        $initials = collect(explode(' ', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => substr($part, 0, 1))
            ->implode('');
    @endphp

    <div
        class="mx-auto min-h-screen max-w-[430px] pb-32 transition duration-300 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12"
        x-data="{
            darkMode: false,
            telegramConnected: @js($telegramStatus['connected'] ?? false),
            spendingAlerts: true,
            weeklySummary: true,
            botReceipts: false,
        }"
        :class="darkMode ? 'bg-[#5C1632] text-white' : ''"
    >
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Profile</h1>
                </div>

                <button
                    type="button"
                    aria-label="Open profile menu"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#093C5D] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 21a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="grid gap-7 px-5 pt-28 lg:max-w-7xl lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:pt-36">
            @if (session('status') === 'telegram-disconnected')
                <div class="rounded-2xl border border-[#BFEDE5] bg-[#DFF8F4] px-4 py-3 text-sm font-bold text-[#007A53] shadow-[0_10px_24px_rgba(9,60,93,0.06)] lg:col-span-2">
                    Telegram disconnected.
                </div>
            @endif

            <section aria-labelledby="profile-card-heading" class="relative overflow-hidden rounded-2xl bg-[#B8336A] p-5 text-white shadow-[0_18px_34px_rgba(184,51,106,0.18)]">
                <div class="absolute -right-12 -top-14 h-36 w-36 rounded-full bg-white/14 blur-2xl"></div>
                {{-- <div class="absolute -bottom-16 left-8 h-36 w-36 rounded-full bg-[#6FD1D7]/22 blur-3xl"></div> --}}

                <div class="relative">
                    <div class="flex items-start gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-4 border-white/55 bg-white/16 text-2xl font-extrabold shadow-[0_14px_28px_rgba(0,0,0,0.12)] backdrop-blur">
                            {{ $initials ?: 'U' }}
                        </div>

                        <div class="min-w-0 flex-1">
                            {{-- <p class="text-xs font-bold uppercase tracking-[0.16em] text-white/68">Premium Member</p> --}}
                            <h2 id="profile-card-heading" class="mt-1 truncate text-2xl font-extrabold tracking-normal">{{ $displayName }}</h2>
                            <p class="mt-1 truncate text-sm font-semibold text-white/76">{{ $email }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Saved</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $profileStats['savings_rate'] ?? 0 }}%</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Bots</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $profileStats['connected_bots'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Txns</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $profileStats['monthly_transactions'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section aria-labelledby="appearance-heading" class="rounded-2xl border border-white/80 bg-white/76 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Appearance</p>
                        <h2 id="appearance-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Theme preview</h2>
                        <p class="mt-1 text-sm font-semibold text-[#72777E]" x-text="darkMode ? 'Previewing berry night mode' : 'Pastel scrapbook mode active'"></p>
                    </div>

                    <button
                        type="button"
                        class="relative h-8 w-14 shrink-0 rounded-full transition duration-200"
                        :class="darkMode ? 'bg-[#093C5D]' : 'bg-[#D8E4E8]'"
                        @click="darkMode = ! darkMode"
                        aria-label="Toggle dark mode"
                    >
                        <span class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition duration-200" :class="darkMode ? 'left-7' : 'left-1'"></span>
                    </button>
                </div>
            </section>

            <section aria-labelledby="connections-heading" class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Connections</p>
                    <h2 id="connections-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Connected Accounts</h2>
                </div>

                <div class="grid gap-4">
                    <article class="rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#E7F3FF] text-[#1D78C1]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M21 4 3 11.2l6.8 2.4M21 4l-4.8 16-6.4-6.4M21 4 9.8 13.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-extrabold text-[#181C1E]">Telegram</h3>
                                    <p class="mt-1 text-sm font-semibold text-[#72777E]">
                                        @if ($telegramStatus['connected'] ?? false)
                                            {{ $telegramStatus['username'] ? '@'.$telegramStatus['username'] : 'Telegram linked' }}
                                        @else
                                            {{ $telegramStatus['last_sync'] ?? 'Waiting for first message' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ ($telegramStatus['connected'] ?? false) ? 'bg-[#DFF8F4] text-[#007A53]' : 'bg-[#FFF1D9] text-[#9A5C00]' }}">
                                {{ ($telegramStatus['connected'] ?? false) ? 'Connected' : 'Not linked' }}
                            </span>
                        </div>

                        @if ($telegramStatus['connected'] ?? false)
                            <div class="mt-4 rounded-2xl border border-[#BFEDE5] bg-[#DFF8F4] p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#007A53]">Last sync</p>
                                        <p class="mt-1 text-sm font-extrabold text-[#093C5D]">{{ $telegramStatus['last_sync'] ?? 'Waiting for first message' }}</p>
                                    </div>

                                    <form method="POST" action="{{ route('profile.telegram.disconnect') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="rounded-full border border-[#F4B3B3] bg-white px-4 py-2 text-xs font-extrabold text-[#BA1A1A] transition hover:bg-[#FFF5F5] active:scale-95"
                                        >
                                            Disconnect
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 rounded-2xl border border-[#DCE8EB] bg-[#F7FAFC] p-4 sm:p-5" x-data="{ copied: false, command: @js($telegramStatus['link_command']) }">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Connect bot</p>
                                        <h4 class="mt-1 text-base font-extrabold text-[#181C1E]">Sambungkan Telegram ke akun ini</h4>
                                        <p class="mt-1 text-sm font-semibold leading-5 text-[#72777E]">Buka bot, tekan Start, lalu kirim command koneksi dari halaman ini.</p>
                                    </div>

                                    <a
                                        href="{{ $telegramStatus['link_url'] ?: 'https://t.me/Eclairs11_bot' }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full bg-[#093C5D] px-4 py-3 text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(9,60,93,0.18)] transition hover:bg-[#0C6680] active:scale-[0.98] sm:w-auto"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M21 4 3 11.2l6.8 2.4M21 4l-4.8 16-6.4-6.4M21 4 9.8 13.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Open Telegram Bot
                                    </a>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div class="rounded-xl border border-[#E7EEF2] bg-white/80 p-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#E7F3FF] text-xs font-extrabold text-[#1D78C1]">1</span>
                                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.1em] text-[#72777E]">Buka bot</p>
                                        <p class="mt-1 text-sm font-semibold leading-5 text-[#5B6067]">Tekan tombol Open Telegram Bot.</p>
                                    </div>
                                    <div class="rounded-xl border border-[#E7EEF2] bg-white/80 p-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#DFF8F4] text-xs font-extrabold text-[#007A53]">2</span>
                                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.1em] text-[#72777E]">Mulai chat</p>
                                        <p class="mt-1 text-sm font-semibold leading-5 text-[#5B6067]">Tekan Start atau Mulai di Telegram.</p>
                                    </div>
                                    <div class="rounded-xl border border-[#E7EEF2] bg-white/80 p-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#FFF1D9] text-xs font-extrabold text-[#9A5C00]">3</span>
                                        <p class="mt-2 text-xs font-extrabold uppercase tracking-[0.1em] text-[#72777E]">Kirim command</p>
                                        <p class="mt-1 text-sm font-semibold leading-5 text-[#5B6067]">Salin command lalu kirim ke chat bot.</p>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-xl border border-[#DCE8EB] bg-white/90 p-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Telegram command</p>
                                            <p class="mt-1 break-all font-mono text-sm font-extrabold text-[#093C5D]">{{ $telegramStatus['link_command'] }}</p>
                                            @if ($telegramStatus['link_expires_at'])
                                                <p class="mt-1 text-xs font-semibold text-[#72777E]">Valid until {{ $telegramStatus['link_expires_at'] }}</p>
                                            @endif
                                        </div>

                                        <button
                                            type="button"
                                            class="flex h-11 shrink-0 items-center justify-center gap-2 rounded-full border border-[#DCE8EB] bg-white px-4 text-sm font-extrabold text-[#093C5D] transition hover:bg-[#EEF4F7] active:scale-95"
                                            @click="(navigator.clipboard ? navigator.clipboard.writeText(command) : Promise.resolve()).finally(() => { copied = true; setTimeout(() => copied = false, 1400) })"
                                            aria-label="Copy Telegram command"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M8 8V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3M6 9h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                            </svg>
                                            <span x-text="copied ? 'Copied' : 'Copy'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </article>

                    <a href="{{ route('bot.index') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl transition hover:opacity-85 active:scale-[0.99]">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#EAF7F8] text-[#093C5D]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M8 9h8M8 13h5M7 18l-4 3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3H7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-[#181C1E]">Bot Assistant</h3>
                                <p class="mt-1 text-sm font-semibold text-[#72777E]">Activity and automation</p>
                            </div>
                        </div>
                        <svg class="h-5 w-5 shrink-0 text-[#3B7597]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </section>

            <section aria-labelledby="notifications-heading" class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Preferences</p>
                    <h2 id="notifications-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Notifications</h2>
                </div>

                <div class="space-y-3">
                    @foreach ($notificationSettings as $setting)
                        <article class="flex items-center justify-between gap-4 rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_14px_30px_rgba(9,60,93,0.07)] backdrop-blur-xl">
                            <div class="min-w-0">
                                <h3 class="text-base font-extrabold text-[#181C1E]">{{ $setting['title'] }}</h3>
                                <p class="mt-1 text-sm font-medium leading-5 text-[#72777E]">{{ $setting['caption'] }}</p>
                            </div>

                            <button
                                type="button"
                                class="relative h-8 w-14 shrink-0 rounded-full transition duration-200"
                                :class="{{ $setting['key'] }} ? 'bg-[#007A53]' : 'bg-[#D8E4E8]'"
                                @click="{{ $setting['key'] }} = ! {{ $setting['key'] }}"
                                aria-label="Toggle {{ $setting['title'] }}"
                            >
                                <span class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition duration-200" :class="{{ $setting['key'] }} ? 'left-7' : 'left-1'"></span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="export-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#EAF7F8] text-[#093C5D]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 4v10M8 10l4 4 4-4M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 id="export-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Export report</h2>
                        <p class="mt-1 text-sm font-semibold leading-6 text-[#72777E]">Download a clean PDF summary for income, expenses, and bot logs.</p>
                        <button type="button" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#093C5D] px-5 py-3 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 4v10M8 10l4 4 4-4M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Export May Report
                        </button>
                    </div>
                </div>
            </section>

            <section aria-labelledby="account-settings-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <h2 id="account-settings-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Account Settings</h2>

                <div class="mt-5 divide-y divide-[#E7EEF2]">
                    @foreach ($accountSettings as $setting)
                        <a href="{{ $setting['href'] ?? '#' }}" class="flex items-center justify-between gap-4 py-4 transition hover:opacity-80 active:scale-[0.99]">
                            <div class="min-w-0">
                                <h3 class="text-base font-extrabold text-[#181C1E]">{{ $setting['label'] }}</h3>
                                <p class="mt-1 truncate text-sm font-semibold text-[#72777E]">{{ $setting['caption'] }}</p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-[#3B7597]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="logout-heading">
                <h2 id="logout-heading" class="sr-only">Logout</h2>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-3 rounded-2xl border border-[#FFD3D3] bg-white/80 px-5 py-4 text-base font-extrabold text-[#BA1A1A] shadow-[0_14px_30px_rgba(186,26,26,0.08)] backdrop-blur-xl transition duration-200 hover:bg-[#FFF5F5] active:scale-[0.98]"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 17 5 12l5-5M5 12h12M14 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </section>
        </main>
    </div>

    <x-bottom-nav active="profile" />
</x-app-layout>
