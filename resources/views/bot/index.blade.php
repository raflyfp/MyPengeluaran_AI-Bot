<x-app-layout>
    @php
        $automations = [
            [
                'key' => 'autoCategory',
                'title' => 'Auto category matching',
                'caption' => 'Match messages like "kopi 18rb" to the right category.',
                'enabled' => true,
            ],
            [
                'key' => 'instantReply',
                'title' => 'Instant Telegram reply',
                'caption' => 'Send a confirmation after each saved transaction.',
                'enabled' => true,
            ],
            [
                'key' => 'reviewFailed',
                'title' => 'Review unclear messages',
                'caption' => 'Flag messages that do not contain a clear amount.',
                'enabled' => true,
            ],
        ];
    @endphp

    <div
        class="mx-auto min-h-screen max-w-[430px] pb-32 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12"
        x-data="{
            telegramConnected: @js($telegram['connected']),
            autoCategory: true,
            instantReply: true,
            reviewFailed: true,
        }"
    >
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Bot Assistant</h1>
                </div>

                <button
                    type="button"
                    aria-label="Open bot status"
                    class="relative flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#093C5D] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <span class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full border-2 border-white {{ $telegram['connected'] ? 'bg-[#16B69C]' : 'bg-[#9A5C00]' }}"></span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M8 9h8M8 13h5M7 18l-4 3V6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v9a3 3 0 0 1-3 3H7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="grid gap-7 px-5 pt-28 lg:max-w-7xl lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:pt-36">
            <section aria-labelledby="assistant-status-heading" class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#093C5D] via-[#0C6680] to-[#16B69C] p-5 text-white shadow-[0_22px_44px_rgba(9,60,93,0.22)]">
                <div class="absolute -right-12 -top-14 h-36 w-36 rounded-full bg-white/14 blur-2xl"></div>
                <div class="absolute -bottom-20 left-8 h-40 w-40 rounded-full bg-[#6FD1D7]/20 blur-3xl"></div>

                <div class="relative">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-white/70">Realtime Sync</p>
                            <h2 id="assistant-status-heading" class="mt-1 text-2xl font-extrabold tracking-normal" x-text="telegramConnected ? 'Telegram online' : 'Waiting for Telegram'"></h2>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/18 px-3 py-1.5 text-xs font-extrabold backdrop-blur">
                            <span class="h-2 w-2 rounded-full {{ $telegram['connected'] ? 'bg-[#6FFBBE] shadow-[0_0_18px_rgba(111,251,190,0.9)]' : 'bg-[#FFD87A]' }}"></span>
                            {{ $telegram['connected'] ? 'Live' : 'Ready' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Synced</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $telegram['total_messages'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Accuracy</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $telegram['accuracy'] }}%</p>
                        </div>
                        <div class="rounded-2xl bg-white/12 p-3 backdrop-blur">
                            <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-white/65">Review</p>
                            <p class="mt-2 text-xl font-extrabold">{{ $telegram['failed_messages'] }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section aria-labelledby="integration-heading" class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Channel</p>
                    <h2 id="integration-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Telegram Integration</h2>
                </div>

                <article class="rounded-2xl border border-white/80 bg-white/76 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#E7F3FF] text-[#1D78C1]">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M21 4 3 11.2l6.8 2.4M21 4l-4.8 16-6.4-6.4M21 4 9.8 13.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-extrabold text-[#181C1E]">Telegram</h3>
                                <p class="mt-0.5 text-sm font-semibold text-[#72777E]">Send: makan 25000, kopi 18rb, gaji 5000000</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="relative h-8 w-14 rounded-full transition duration-200"
                            :class="telegramConnected ? 'bg-[#007A53]' : 'bg-[#D8E4E8]'"
                            @click="telegramConnected = ! telegramConnected"
                            aria-label="Toggle Telegram visual status"
                        >
                            <span class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition duration-200" :class="telegramConnected ? 'left-7' : 'left-1'"></span>
                        </button>
                    </div>

                    <div class="mt-5 flex items-center justify-between rounded-2xl bg-[#F7FAFC] p-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Last sync</p>
                            <p class="mt-1 text-sm font-extrabold text-[#093C5D]">{{ $telegram['last_sync'] }}</p>
                        </div>
                        <span class="rounded-full bg-[#DFF8F4] px-3 py-1 text-xs font-extrabold text-[#007A53]" x-text="telegramConnected ? 'Active' : 'Paused'"></span>
                    </div>
                </article>
            </section>

            <section aria-labelledby="preview-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <h2 id="preview-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Expense Logging Preview</h2>

                <div class="mt-5 space-y-4">
                    <div class="max-w-[82%] rounded-2xl rounded-bl-md bg-[#EAF3F6] p-4">
                        <p class="text-sm font-semibold leading-6 text-[#243135]">kopi 18rb</p>
                    </div>

                    <div class="ml-auto max-w-[88%] rounded-2xl rounded-br-md bg-gradient-to-br from-[#093C5D] to-[#0D8B7D] p-4 text-white shadow-[0_14px_30px_rgba(9,60,93,0.18)]">
                        <p class="text-sm font-bold">Logged as Food & Drink</p>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl bg-white/14 p-3">
                                <p class="font-bold uppercase tracking-[0.12em] text-white/65">Amount</p>
                                <p class="mt-1 text-base font-extrabold">Rp 18.000</p>
                            </div>
                            <div class="rounded-xl bg-white/14 p-3">
                                <p class="font-bold uppercase tracking-[0.12em] text-white/65">Source</p>
                                <p class="mt-1 text-base font-extrabold">Telegram</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section aria-labelledby="automation-heading" class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Automation</p>
                    <h2 id="automation-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Smart Rules</h2>
                </div>

                <div class="space-y-3">
                    @foreach ($automations as $automation)
                        <article class="flex items-center justify-between gap-4 rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_14px_30px_rgba(9,60,93,0.07)] backdrop-blur-xl">
                            <div class="min-w-0">
                                <h3 class="text-base font-extrabold text-[#181C1E]">{{ $automation['title'] }}</h3>
                                <p class="mt-1 text-sm font-medium leading-5 text-[#72777E]">{{ $automation['caption'] }}</p>
                            </div>

                            <button
                                type="button"
                                class="relative h-8 w-14 shrink-0 rounded-full transition duration-200"
                                :class="{{ $automation['key'] }} ? 'bg-[#007A53]' : 'bg-[#D8E4E8]'"
                                @click="{{ $automation['key'] }} = ! {{ $automation['key'] }}"
                                aria-label="Toggle {{ $automation['title'] }}"
                            >
                                <span class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition duration-200" :class="{{ $automation['key'] }} ? 'left-7' : 'left-1'"></span>
                            </button>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="activity-heading" class="space-y-4">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Timeline</p>
                        <h2 id="activity-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Latest Telegram Activity</h2>
                    </div>
                    <span class="rounded-full bg-[#DFF8F4] px-3 py-1 text-xs font-extrabold text-[#007A53]">DB Live</span>
                </div>

                <div class="space-y-3">
                    @forelse ($activities as $activity)
                        <article class="rounded-2xl border border-[#E7EEF2] bg-white p-4 shadow-[0_8px_22px_rgba(9,60,93,0.06)]">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">{{ $activity['channel'] }} &bull; {{ $activity['time'] }}</p>
                                    <h3 class="mt-2 truncate text-lg font-extrabold text-[#181C1E]">{{ $activity['title'] }}</h3>
                                    <p class="mt-0.5 truncate text-sm font-semibold text-[#3C4A42]">{{ $activity['detail'] }}</p>
                                </div>
                                <p class="shrink-0 text-right text-base font-extrabold {{ $activity['tone'] === 'income' ? 'text-[#007A53]' : ($activity['status'] === 'failed' ? 'text-[#9A5C00]' : 'text-[#BA1A1A]') }}">{{ $activity['amount'] }}</p>
                            </div>
                        </article>
                    @empty
                        <article class="rounded-2xl border border-white/80 bg-white/76 p-5 text-center shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                            <p class="text-lg font-extrabold text-[#093C5D]">No Telegram activity yet</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-[#72777E]">Send a message to the bot and this timeline will update from the database.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    <x-bottom-nav active="bot" />
</x-app-layout>
