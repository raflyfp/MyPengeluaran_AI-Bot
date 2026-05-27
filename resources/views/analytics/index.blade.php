<x-app-layout>
    @php
        $summaryCards = $summaryCards ?? [
            [
                'label' => 'Total Spending',
                'value' => 'Rp 7.480.000',
                'caption' => 'May 2026',
                'tone' => 'expense',
                'change' => '-8,6%',
            ],
            [
                'label' => 'Net Cashflow',
                'value' => '+Rp 2.320.000',
                'caption' => 'Income minus expense',
                'tone' => 'income',
                'change' => '+12,4%',
            ],
        ];

        $analyticsCards = $analyticsCards ?? [
            ['label' => 'Daily Avg', 'value' => 'Rp 249.300', 'caption' => 'Lower than April', 'tone' => 'blue'],
            ['label' => 'Savings Rate', 'value' => '31%', 'caption' => 'On target', 'tone' => 'green'],
            ['label' => 'Top Category', 'value' => 'Food', 'caption' => 'Rp 2.140.000', 'tone' => 'cyan'],
        ];

        $categories = $categories ?? [
            ['name' => 'Food & Drink', 'amount' => 'Rp 2.140.000', 'share' => 29, 'color' => '#0D8B7D'],
            ['name' => 'Bills & Utilities', 'amount' => 'Rp 1.680.000', 'share' => 22, 'color' => '#093C5D'],
            ['name' => 'Transport', 'amount' => 'Rp 1.120.000', 'share' => 15, 'color' => '#3B7597'],
            ['name' => 'Shopping', 'amount' => 'Rp 980.000', 'share' => 13, 'color' => '#6FD1D7'],
        ];

        $months = $months ?? [
            ['label' => 'Mar', 'income' => 'Rp 8.900.000', 'expense' => 'Rp 6.780.000', 'net' => '+Rp 2.120.000'],
            ['label' => 'Apr', 'income' => 'Rp 9.150.000', 'expense' => 'Rp 8.180.000', 'net' => '+Rp 970.000'],
            ['label' => 'May', 'income' => 'Rp 9.800.000', 'expense' => 'Rp 7.480.000', 'net' => '+Rp 2.320.000'],
        ];

        $apexCharts = $apexCharts ?? [
            'monthlySpending' => ['labels' => [], 'series' => []],
            'categoryBreakdown' => ['labels' => [], 'series' => [], 'colors' => []],
            'weeklyExpenseTrends' => ['labels' => [], 'series' => []],
            'monthlyIncomeVsExpense' => ['labels' => [], 'series' => []],
        ];
    @endphp

    <div class="mx-auto min-h-screen max-w-[430px] pb-32 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12" x-data="{ period: 'M' }">
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Analytics</h1>
                </div>

                <button
                    type="button"
                    aria-label="Export analytics"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#093C5D] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 4v10M8 10l4 4 4-4M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="space-y-7 px-5 pt-28 lg:max-w-7xl lg:px-8 lg:pt-36">
            <section aria-labelledby="spending-summary-heading" class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Overview</p>
                        <h2 id="spending-summary-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Monthly Financials</h2>
                    </div>

                    <div class="flex rounded-full bg-white/72 p-1 shadow-[0_8px_20px_rgba(9,60,93,0.06)] backdrop-blur-xl">
                        @foreach (['W', 'M', 'Y'] as $item)
                            <button
                                type="button"
                                class="h-8 w-9 rounded-full text-sm font-bold transition duration-200"
                                :class="period === @js($item) ? 'bg-[#093C5D] text-white shadow-sm' : 'text-[#3C4A42] hover:bg-white'"
                                @click="period = @js($item)"
                            >
                                {{ $item }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach ($summaryCards as $card)
                        <article class="rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                            <div class="mb-4 flex items-start justify-between gap-2">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">{{ $card['label'] }}</p>
                                <span class="rounded-full px-2 py-1 text-[11px] font-extrabold {{ $card['tone'] === 'income' ? 'bg-[#DFF8F4] text-[#007A53]' : 'bg-[#FFE7E7] text-[#BA1A1A]' }}">
                                    {{ $card['change'] }}
                                </span>
                            </div>
                            <p class="text-[1.35rem] font-extrabold leading-7 tracking-normal {{ $card['tone'] === 'income' ? 'text-[#007A53]' : 'text-[#181C1E]' }}">{{ $card['value'] }}</p>
                            <p class="mt-2 text-xs font-semibold text-[#72777E]">{{ $card['caption'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="breakdown-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="breakdown-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Spending Breakdown</h2>
                        <p class="mt-1 text-sm font-medium text-[#72777E]">Category share this month</p>
                    </div>
                    <span class="rounded-full bg-[#EAF7F8] px-3 py-1 text-xs font-extrabold text-[#007A53]">-8.6%</span>
                </div>

                <div
                    class="grid items-center gap-5 sm:grid-cols-[160px_1fr]"
                    aria-label="ApexCharts donut chart placeholder for spending breakdown"
                >
                    <figure class="relative mx-auto h-40 w-40">
                        <div
                            id="apex-spending-breakdown"
                            data-chart-engine="ApexCharts"
                            data-chart-type="donut"
                            data-chart-payload='@json($apexCharts['categoryBreakdown'])'
                            class="h-40 w-40"
                        ></div>
                        <figcaption class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Spent</span>
                            <span class="mt-1 text-xl font-extrabold text-[#093C5D]">{{ $summaryCards[0]['value'] }}</span>
                        </figcaption>
                    </figure>

                    <div class="space-y-3">
                        @foreach ($categories as $category)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-[#181C1E]">{{ $category['name'] }}</p>
                                        <p class="text-xs font-semibold text-[#72777E]">{{ $category['amount'] }}</p>
                                    </div>
                                </div>
                                <p class="shrink-0 text-sm font-extrabold text-[#093C5D]">{{ $category['share'] }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section aria-labelledby="analytics-cards-heading" class="grid grid-cols-3 gap-3">
                <h2 id="analytics-cards-heading" class="sr-only">Analytics cards</h2>

                @foreach ($analyticsCards as $card)
                    @php
                        $toneClasses = [
                            'blue' => 'from-[#093C5D] to-[#3B7597] text-white',
                            'green' => 'from-[#16B69C] to-[#007A53] text-white',
                            'cyan' => 'from-[#EAF7F8] to-white text-[#093C5D]',
                        ][$card['tone']];
                    @endphp

                    <article class="rounded-2xl border border-white/70 bg-gradient-to-br {{ $toneClasses }} p-4 shadow-[0_14px_30px_rgba(9,60,93,0.09)]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.12em] opacity-75">{{ $card['label'] }}</p>
                        <p class="mt-3 text-lg font-extrabold tracking-normal">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold opacity-75">{{ $card['caption'] }}</p>
                    </article>
                @endforeach
            </section>

            <section aria-labelledby="weekly-trend-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="weekly-trend-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Weekly Trends</h2>
                        <p class="mt-1 text-sm font-medium text-[#72777E]">Expense movement by day</p>
                    </div>
                    <span class="rounded-full bg-[#DFF8F4] px-3 py-1 text-xs font-extrabold text-[#007A53]">Healthy</span>
                </div>

                <div
                    id="apex-weekly-trends"
                    data-chart-engine="ApexCharts"
                    data-chart-type="line"
                    data-chart-payload='@json($apexCharts['weeklyExpenseTrends'])'
                    class="relative h-48 overflow-hidden rounded-2xl bg-gradient-to-b from-[#F7FEFF] to-white"
                    aria-label="ApexCharts area chart placeholder for weekly trends"
                ></div>
            </section>

            <section aria-labelledby="monthly-chart-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="monthly-chart-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Income vs Expense</h2>
                        <p class="mt-1 text-sm font-medium text-[#72777E]">Monthly comparison</p>
                    </div>
                    <span class="rounded-full bg-[#EAF7F8] px-3 py-1 text-xs font-extrabold text-[#093C5D]">6M</span>
                </div>

                <div
                    id="apex-monthly-income-expense"
                    data-chart-engine="ApexCharts"
                    data-chart-type="bar"
                    data-chart-payload='@json($apexCharts['monthlyIncomeVsExpense'])'
                    class="h-52 rounded-2xl bg-gradient-to-b from-[#F7FEFF] to-white"
                    aria-label="ApexCharts bar chart for monthly income versus expense"
                ></div>
            </section>

            <section aria-labelledby="category-stats-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <h2 id="category-stats-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Category Statistics</h2>

                <div class="mt-5 space-y-5">
                    @foreach ($categories as $category)
                        <article>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <p class="text-sm font-extrabold text-[#181C1E]">{{ $category['name'] }}</p>
                                <p class="text-sm font-bold text-[#72777E]">{{ $category['amount'] }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-[#EAF3F6]">
                                <div class="h-full rounded-full" style="width: {{ $category['share'] }}%; background-color: {{ $category['color'] }}"></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="monthly-overview-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                <h2 id="monthly-overview-heading" class="text-xl font-bold tracking-normal text-[#181C1E]">Monthly Overview</h2>

                <div class="mt-5 space-y-3">
                    @foreach ($months as $month)
                        <article class="rounded-2xl bg-[#F7FAFC] p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-base font-extrabold text-[#093C5D]">{{ $month['label'] }}</p>
                                <p class="text-sm font-extrabold text-[#007A53]">{{ $month['net'] }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#72777E]">Income</p>
                                    <p class="mt-1 font-extrabold text-[#007A53]">{{ $month['income'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#72777E]">Expense</p>
                                    <p class="mt-1 font-extrabold text-[#181C1E]">{{ $month['expense'] }}</p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>
    </div>

    <x-bottom-nav active="analytics" />
</x-app-layout>
