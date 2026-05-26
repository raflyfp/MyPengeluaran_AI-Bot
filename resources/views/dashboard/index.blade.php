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
        <header class="fixed inset-x-0 top-0 z-40 mx-auto w-full max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-[#093C5D] to-[#6FD1D7] text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(9,60,93,0.16)]">
                        {{ $initials ?: 'U' }}
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">Good Morning</p>
                        <h1 class="mt-0.5 text-xl font-extrabold tracking-normal text-[#006C49]">{{ $displayName }}</h1>
                    </div>
                </div>

                <button
                    type="button"
                    aria-label="Open notifications"
                    class="relative flex h-12 w-12 items-center justify-center rounded-full bg-white/70 text-[#007A53] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <span class="absolute right-3 top-3 h-2.5 w-2.5 rounded-full border-2 border-white bg-[#6FD1D7]"></span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M18 9a6 6 0 1 0-12 0v4.8L4.5 17h15L18 13.8V9ZM10 20h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="grid min-w-0 gap-8 px-5 pt-28 lg:max-w-7xl lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:px-8 lg:pt-36">
            <section aria-labelledby="balance-heading" class="min-w-0 space-y-4">
                <x-balance-card
                    label="Total Balance"
                    :amount="$summary['formatted_current_balance']"
                    change="+2,4%"
                    caption="vs bulan lalu"
                />

                <div class="grid min-w-0 grid-cols-2 gap-3 sm:gap-4">
                    <x-stat-card type="income" label="Income" :amount="$summary['formatted_monthly_income_total']" />
                    <x-stat-card type="expense" label="Expense" :amount="$summary['formatted_monthly_expense_total']" />
                </div>
            </section>

            <x-insight-card title="Smart Insight">
                @if ($topCategory)
                    Kategori terbesar bulan ini adalah <strong class="font-extrabold text-[#007A53]">{{ $topCategory['name'] }}</strong> sebesar {{ $topCategory['formatted_total'] }}.
                @else
                    Belum ada transaksi bulan ini. Mulai catat pemasukan dan pengeluaran agar insight keuangan muncul otomatis.
                @endif
            </x-insight-card>

            <section id="analytics" aria-labelledby="cashflow-heading" x-data="{ period: 'M' }" class="min-w-0 rounded-2xl border border-white bg-white/86 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="mb-5 flex min-w-0 items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h2 id="cashflow-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Cashflow</h2>
                        <p class="mt-1 truncate text-sm font-medium text-[#72777E]">
                            {{ ($cashflow['has_data'] ?? false) ? '7 hari terakhir dari transaksi real' : 'Belum ada data 7 hari terakhir' }}
                        </p>
                    </div>

                    <div class="flex shrink-0 rounded-full bg-[#F2F7F8] p-1">
                        @foreach (['W', 'M', 'Y'] as $period)
                            <button
                                type="button"
                                class="h-8 w-9 rounded-full text-sm font-bold transition duration-200"
                                :class="period === @js($period) ? 'bg-[#007A53] text-white shadow-sm' : 'text-[#3C4A42] hover:bg-white'"
                                @click="period = @js($period)"
                            >
                                {{ $period }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <figure class="relative h-44 overflow-hidden rounded-2xl bg-gradient-to-b from-[#F7FEFF] to-white">
                    <svg class="h-full w-full" viewBox="0 0 320 176" fill="none" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M24 42H296M24 82H296M24 122H296" stroke="#DCE8EB" stroke-width="1.5" stroke-dasharray="8 8"/>
                        <polygon points="{{ $cashflow['area_points'] ?? '24,176 24,130 70,104 116,114 162,92 208,124 254,82 296,48 296,176' }}" fill="url(#cashflow-area)"/>
                        <polyline points="{{ $cashflow['line_points'] ?? '24,130 70,104 116,114 162,92 208,124 254,82 296,48' }}" stroke="#16B69C" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="cashflow-area" x1="160" y1="20" x2="160" y2="176" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6FD1D7" stop-opacity="0.34"/>
                                <stop offset="1" stop-color="#6FD1D7" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <figcaption class="sr-only">Cashflow chart based on the last seven days of transaction data.</figcaption>
                </figure>
            </section>

            <section id="transactions" aria-labelledby="transactions-heading" class="min-w-0">
                <div class="mb-4 flex min-w-0 items-center justify-between">
                    <h2 id="transactions-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Recent Activity</h2>
                    <a href="{{ route('transactions.index') }}" class="text-sm font-bold text-[#007A53] transition hover:text-[#093C5D]">See All</a>
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
                        <article class="rounded-2xl border border-[#E7EEF2] bg-white p-5 text-center shadow-[0_8px_22px_rgba(9,60,93,0.06)]">
                            <p class="text-sm font-semibold text-[#72777E]">Belum ada transaksi terbaru.</p>
                        </article>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    <x-bottom-nav active="home" />
</x-app-layout>
