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

    <div class="mx-auto min-h-screen max-w-[430px] pb-32">
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl">
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

        <main class="space-y-8 px-5 pt-28">
            <section aria-labelledby="balance-heading" class="space-y-4">
                <x-balance-card
                    label="Total Balance"
                    :amount="$summary['formatted_current_balance']"
                    change="+2,4%"
                    caption="vs bulan lalu"
                />

                <div class="grid grid-cols-2 gap-4">
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

            <section id="analytics" aria-labelledby="cashflow-heading" x-data="{ period: 'M' }" class="rounded-2xl border border-white bg-white/86 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 id="cashflow-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Cashflow</h2>
                        <p class="mt-1 text-sm font-medium text-[#72777E]">Ringkasan pemasukan dan pengeluaran</p>
                    </div>

                    <div class="flex rounded-full bg-[#F2F7F8] p-1">
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
                        <path d="M24 130 C52 98 78 94 106 112 C136 132 158 92 184 88 C218 84 218 154 248 130 C272 110 268 20 296 44 L296 176 L24 176 Z" fill="url(#cashflow-area)"/>
                        <path d="M24 130 C52 98 78 94 106 112 C136 132 158 92 184 88 C218 84 218 154 248 130 C272 110 268 20 296 44" stroke="#16B69C" stroke-width="5" stroke-linecap="round"/>
                        <defs>
                            <linearGradient id="cashflow-area" x1="160" y1="20" x2="160" y2="176" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6FD1D7" stop-opacity="0.34"/>
                                <stop offset="1" stop-color="#6FD1D7" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <figcaption class="sr-only">Cashflow chart placeholder showing a rising monthly trend.</figcaption>
                </figure>
            </section>

            <section id="transactions" aria-labelledby="transactions-heading">
                <div class="mb-4 flex items-center justify-between">
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
