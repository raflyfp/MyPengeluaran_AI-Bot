<x-app-layout>
    @php
        $displayName = $user?->name ?? auth()->user()?->name ?? 'User';
        $initials = collect(explode(' ', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => substr($part, 0, 1))
            ->implode('');
        $topCategory = $summary['category_spending_breakdown']->first();
    @endphp

    <div class="mx-auto min-h-screen w-full max-w-[430px] overflow-x-hidden pb-32 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12">
        <header class="fixed inset-x-0 top-0 z-40 mx-auto w-full max-w-[430px] border-b border-white/70 bg-[#FFF7EA]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-white text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(184,51,106,0.16)]">
                        <x-application-logo class="h-full w-full object-cover" />
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9A6275]">Welcome back,</p>
                        <h1 class="mt-0.5 text-xl font-extrabold tracking-normal text-[#006C49]">{{ $displayName }}</h1>
                    </div>
                </div>

                <button
                    type="button"
                    aria-label="Open notifications"
                    class="relative flex h-12 w-12 items-center justify-center rounded-full bg-white/70 text-[#2E9F86] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <span class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full border-2 border-white bg-[#7EC7E8]"></span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M18 9a6 6 0 1 0-12 0v4.8L4.5 17h15L18 13.8V9ZM10 20h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="grid min-w-0 gap-8 px-5 pt-28 lg:items-start lg:gap-6 lg:max-w-7xl lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:px-6 lg:pt-32">
            <section aria-labelledby="balance-heading" class="min-w-0 space-y-4">
                <x-balance-card
                    label="Total Balance"
                    :amount="$summary['formatted_current_balance']"
                    :change="$summary['balance_change_label']"
                    caption="vs bulan lalu"
                />

                <div class="grid min-w-0 grid-cols-2 gap-3 sm:gap-4">
                    <x-stat-card type="income" label="Income" :amount="$summary['formatted_monthly_income_total']" />
                    <x-stat-card type="expense" label="Expense" :amount="$summary['formatted_monthly_expense_total']" />
                </div>
            </section>

            <x-insight-card title="Smart Insight">
                @if ($topCategory)
                    Kategori terbesar bulan ini adalah <strong class="font-extrabold text-[#2E9F86]">{{ $topCategory['name'] }}</strong> sebesar {{ $topCategory['formatted_total'] }}.
                @else
                    Belum ada transaksi bulan ini. Mulai catat pemasukan dan pengeluaran agar insight keuangan muncul otomatis.
                @endif
            </x-insight-card>

            <section
                id="analytics"
                aria-labelledby="cashflow-heading"
                x-data="{ period: @js($cashflow['default_period']), cashflow: @js($cashflow['periods']) }"
                class="min-w-0 rounded-2xl border border-white bg-white/86 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)]"
            >
                <div class="mb-5 flex min-w-0 items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h2 id="cashflow-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">Cashflow</h2>
                        <p class="mt-1 truncate text-sm font-medium text-[#9B7A82]" x-text="cashflow[period].caption"></p>
                    </div>

                    <div class="flex shrink-0 rounded-full bg-[#FFF1F6] p-1">
                        @foreach (['W', 'M', 'Y'] as $period)
                            <button
                                type="button"
                                class="h-8 w-9 rounded-full text-sm font-bold transition duration-200"
                                :class="period === @js($period) ? 'bg-[#2E9F86] text-white shadow-sm' : 'text-[#684C59] hover:bg-white'"
                                @click="period = @js($period)"
                            >
                                {{ $period }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <figure class="relative h-44 overflow-hidden rounded-2xl bg-white">
                    <svg class="h-full w-full" viewBox="0 0 320 176" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M24 42H296M24 82H296M24 122H296" stroke="#F5C9D6" stroke-width="1.5" stroke-dasharray="8 8"/>
                        <polygon :points="cashflow[period].area_points" fill="url(#cashflow-area)"/>
                        <polyline :points="cashflow[period].line_points" stroke="#82D9A8" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="cashflow-area" x1="160" y1="20" x2="160" y2="176" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#7EC7E8" stop-opacity="0.34"/>
                                <stop offset="1" stop-color="#7EC7E8" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <figcaption class="sr-only">Cashflow chart based on the selected period.</figcaption>
                </figure>
            </section>

            <section id="transactions" aria-labelledby="transactions-heading" class="min-w-0">
                <div class="mb-4 flex min-w-0 items-center justify-between">
                    <h2 id="transactions-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">Recent Activity</h2>
                    <a href="{{ route('transactions.index') }}" class="text-sm font-bold text-[#2E9F86] transition hover:text-[#B8336A]">See All</a>
                </div>

                <div class="space-y-4">
                    @forelse ($transactions as $transaction)
                        <x-transaction-card
                            :title="$transaction['title']"
                            :category="$transaction['category']"
                            :time="$transaction['time']"
                            :amount="$transaction['amount']"
                            :type="$transaction['type']"
                            :icon="$transaction['icon']"
                        />
                    @empty
                        <article class="rounded-2xl border border-[#F8D9E3] bg-white p-5 text-center shadow-[0_8px_22px_rgba(9,60,93,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(9,60,93,0.1)]">
                            <p class="text-sm font-semibold text-[#9B7A82]">Belum ada transaksi terbaru.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    <x-bottom-nav active="home" />

    <!-- CSS styles for custom telegram connection popup modal -->
    <style>
        .telegram-modal-backdrop {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            z-index: 9999 !important;
            display: flex;
            align-items: center !important;
            justify-content: center !important;
            padding: 1rem !important;
            background-color: rgba(75, 39, 53, 0.5) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
        }

        .telegram-modal-card {
            position: relative !important;
            width: 100% !important;
            max-width: 22rem !important;
            border-radius: 2rem !important;
            border: 4px solid #4B2735 !important;
            background-color: #FDF8F5 !important;
            padding: 2rem !important;
            box-shadow: 8px 8px 0px #4B2735 !important;
            text-align: center !important;
            color: #4B2735 !important;
            z-index: 10000 !important;
        }

        .app-dark .telegram-modal-card {
            background-color: #3f3543 !important;
            border-color: #ffffff !important;
            box-shadow: 8px 8px 0px #ffffff !important;
            color: #ffffff !important;
        }
    </style>

    <!-- Modal Alert Overlay for Telegram Bot connection status -->
    <div
        x-data="{
            showTelegramPopup: !@js($telegramStatus['connected'] ?? false),
            copied: false,
            command: @js($telegramStatus['link_command'] ?? ''),
            dismiss() {
                this.showTelegramPopup = false;
                document.body.classList.remove('overflow-hidden');
            }
        }"
        x-init="if (showTelegramPopup) { document.body.classList.add('overflow-hidden'); }"
        x-show="showTelegramPopup"
        :style="showTelegramPopup ? 'display: flex !important;' : 'display: none !important;'"
        style="display: none !important;"
        class="telegram-modal-backdrop"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
    >
        <div
            class="telegram-modal-card"
            @click.away="dismiss()"
        >
            <!-- Close Button -->
            <button
                @click="dismiss()"
                class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full border-2 border-[#4B2735] bg-white text-[#4B2735] shadow-[2px_2px_0px_#4B2735] transition duration-150 hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_#4B2735] active:translate-x-0 active:translate-y-0 active:shadow-[1px_1px_0px_#4B2735] dark:border-white dark:bg-[#463845] dark:text-white dark:shadow-[2px_2px_0px_#ffffff] dark:hover:shadow-[3px_3px_0px_#ffffff]"
                aria-label="Tutup popup"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>

            <!-- Cute header graphic/icon -->
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-[3px] border-[#4B2735] bg-[#E7F3FF] text-[#1D78C1] shadow-[4px_4px_0px_#4B2735] dark:border-white dark:bg-sky-950 dark:text-sky-300 dark:shadow-[4px_4px_0px_#ffffff]">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M21 4 3 11.2l6.8 2.4M21 4l-4.8 16-6.4-6.4M21 4 9.8 13.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <h3 class="text-xl font-extrabold text-[#4B2735] dark:text-white">Sambungkan Telegram!</h3>
            <p class="mt-2 text-sm font-semibold text-[#9B7A82] dark:text-[#eadde4] leading-relaxed">
                Catat transaksi harianmu lebih praktis lewat chat Telegram. Dapatkan laporan keuangan bulanan otomatis langsung ke HP-mu!
            </p>

            @if (!empty($telegramStatus['link_command']))
                <!-- Copy-paste section for command -->
                <div class="mt-4 rounded-2xl border-2 border-[#4B2735] bg-white p-3 dark:border-white dark:bg-[#463845]">
                    <p class="text-left text-xs font-bold uppercase tracking-[0.12em] text-[#9B7A82] dark:text-[#eadde4]">Salin Kode Koneksi</p>
                    <div class="mt-1.5 flex items-center justify-between gap-3">
                        <code class="break-all text-left font-mono text-sm font-extrabold text-[#B8336A] dark:text-[#ffbfd1]">{{ $telegramStatus['link_command'] }}</code>
                        <button
                            type="button"
                            class="flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-full border-2 border-[#4B2735] bg-white px-3 text-xs font-extrabold text-[#4B2735] shadow-[2px_2px_0px_#4B2735] transition duration-150 hover:-translate-y-0.5 hover:shadow-[3px_3px_0px_#4B2735] active:translate-x-0 active:translate-y-0 active:shadow-[1px_1px_0px_#4B2735] dark:border-white dark:bg-[#3f3543] dark:text-white dark:shadow-[2px_2px_0px_#ffffff]"
                            @click="(navigator.clipboard ? navigator.clipboard.writeText(command) : Promise.resolve()).finally(() => { copied = true; setTimeout(() => copied = false, 1400) })"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 8V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-3M6 9h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            <span x-text="copied ? 'Disalin' : 'Salin'"></span>
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-[10px] font-bold text-[#9B7A82] dark:text-[#eadde4] leading-relaxed">
                    *Klik "Hubungkan Sekarang", tekan Start pada Telegram, lalu kirim kode di atas.
                </p>
            @endif

            <div class="mt-6 flex flex-col gap-2.5">
                <a
                    href="{{ $telegramStatus['link_url'] ?: 'https://t.me/Eclairs11_bot' }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="dismiss()"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-full border-2 border-[#4B2735] bg-[#2E9F86] text-sm font-extrabold text-white shadow-[4px_4px_0px_#4B2735] transition duration-150 hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_#4B2735] active:translate-x-0 active:translate-y-0 active:shadow-[2px_2px_0px_#4B2735] dark:border-white dark:shadow-[4px_4px_0px_#ffffff]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M21 4 3 11.2l6.8 2.4M21 4l-4.8 16-6.4-6.4M21 4 9.8 13.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Hubungkan Sekarang
                </a>
                
                <button
                    type="button"
                    @click="dismiss()"
                    class="flex h-12 w-full items-center justify-center rounded-full border-2 border-[#4B2735] bg-white text-sm font-extrabold text-[#4B2735] shadow-[4px_4px_0px_#4B2735] transition duration-150 hover:-translate-y-0.5 hover:shadow-[5px_5px_0px_#4B2735] active:translate-x-0 active:translate-y-0 active:shadow-[2px_2px_0px_#4B2735] dark:border-white dark:bg-[#3f3543] dark:text-white dark:shadow-[4px_4px_0px_#ffffff]"
                >
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
