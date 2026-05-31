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
            ['name' => 'Food & Drink', 'amount' => 'Rp 2.140.000', 'share' => 29, 'color' => '#2E9F86'],
            ['name' => 'Bills & Utilities', 'amount' => 'Rp 1.680.000', 'share' => 22, 'color' => '#B8336A'],
            ['name' => 'Transport', 'amount' => 'Rp 1.120.000', 'share' => 15, 'color' => '#69A7D8'],
            ['name' => 'Shopping', 'amount' => 'Rp 980.000', 'share' => 13, 'color' => '#7EC7E8'],
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
    @php
        $activePeriod = $activePeriod ?? 'M';
        $summaryTitle = [
            'W' => 'Weekly Financials',
            'M' => 'Monthly Financials',
            'Y' => 'Yearly Financials',
        ][$activePeriod] ?? 'Period Financials';
        $breakdownCaption = [
            'W' => 'Category share last 7 days',
            'M' => 'Category share this month',
            'Y' => 'Category share last 12 months',
        ][$activePeriod] ?? 'Category share this period';
        $trendTitle = [
            'W' => 'Weekly Trends',
            'M' => 'Monthly Trends',
            'Y' => 'Yearly Trends',
        ][$activePeriod] ?? 'Expense Trends';
        $trendCaption = [
            'W' => 'Expense movement by day',
            'M' => 'Expense movement this month',
            'Y' => 'Expense movement by month',
        ][$activePeriod] ?? 'Expense movement';
        $incomeExpenseTitle = [
            'W' => 'Weekly Income vs Expense',
            'M' => 'Monthly Income vs Expense',
            'Y' => 'Yearly Income vs Expense',
        ][$activePeriod] ?? 'Income vs Expense';
        $incomeExpenseCaption = [
            'W' => 'Last 7 days comparison',
            'M' => 'This month comparison',
            'Y' => 'Last 12 months comparison',
        ][$activePeriod] ?? 'Period comparison';
        $overviewTitle = [
            'W' => 'Weekly Overview',
            'M' => 'Monthly Overview',
            'Y' => 'Yearly Overview',
        ][$activePeriod] ?? 'Period Overview';
        $overviewCaption = [
            'W' => 'Daily totals for last 7 days',
            'M' => 'Weekly totals for this month',
            'Y' => 'Monthly totals for the last 12 months',
        ][$activePeriod] ?? 'Totals for the selected period';
        $trendAxisLabels = [];
        $trendLabels = $apexCharts['weeklyExpenseTrends']['labels'] ?? [];

        if ($activePeriod === 'M' && count($trendLabels) > 0) {
            $trendLabelStep = count($trendLabels) > 12 ? (int) ceil(count($trendLabels) / 5) : 1;

            foreach ($trendLabels as $index => $label) {
                if ($index % $trendLabelStep === 0 || $index === count($trendLabels) - 1) {
                    $trendAxisLabels[] = $label;
                }
            }
        }

        $periodUrl = fn (string $period) => route('analytics.index', array_filter([
            'period' => $period,
            'month' => request()->query('month'),
        ]));
        $exportUrl = route('analytics.export', array_filter([
            'period' => $activePeriod,
            'month' => request()->query('month'),
        ]));
    @endphp

    <div class="mx-auto min-h-screen max-w-[430px] pb-32 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12">
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#FFF7EA]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9A6275]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#B8336A]">Analytics</h1>
                </div>

                <a
                    href="{{ $exportUrl }}"
                    aria-label="Export analytics"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#B8336A] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 4v10M8 10l4 4 4-4M5 20h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </header>

        <main class="space-y-7 px-5 pt-28 lg:grid lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start lg:gap-6 lg:space-y-0 lg:max-w-7xl lg:px-6 lg:pt-32">
            <section aria-labelledby="spending-summary-heading" class="space-y-4 lg:col-start-1 lg:row-start-1">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Overview</p>
                        <h2 id="spending-summary-heading" class="mt-1 text-xl font-bold tracking-normal text-[#4B2735]">{{ $summaryTitle }}</h2>
                    </div>

                    <div class="flex rounded-full bg-white/72 p-1 shadow-[0_8px_20px_rgba(9,60,93,0.06)] backdrop-blur-xl">
                        @foreach (['W', 'M', 'Y'] as $item)
                            <a
                                href="{{ $periodUrl($item) }}"
                                class="flex h-8 w-9 items-center justify-center rounded-full text-sm font-bold transition duration-200 {{ $activePeriod === $item ? 'bg-[#B8336A] text-white shadow-sm' : 'text-[#684C59] hover:bg-white' }}"
                            >
                                {{ $item }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach ($summaryCards as $card)
                        <article class="rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)]">
                            <div class="mb-4 flex items-start justify-between gap-2">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">{{ $card['label'] }}</p>
                                <span class="rounded-full px-2 py-1 text-[11px] font-extrabold {{ $card['tone'] === 'income' ? 'bg-[#DDF8E8] text-[#2E9F86]' : 'bg-[#FFE4EF] text-[#D93662]' }}">
                                    {{ $card['change'] }}
                                </span>
                            </div>
                            <p class="text-[1.35rem] font-extrabold leading-7 tracking-normal {{ $card['tone'] === 'income' ? 'text-[#2E9F86]' : 'text-[#4B2735]' }}">{{ $card['value'] }}</p>
                            <p class="mt-2 text-xs font-semibold text-[#9B7A82]">{{ $card['caption'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="breakdown-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-start-2 lg:row-start-1">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="breakdown-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">Spending Breakdown</h2>
                        <p class="mt-1 text-sm font-medium text-[#9B7A82]">{{ $breakdownCaption }}</p>
                    </div>
                    <span class="rounded-full bg-[#FFF2C8] px-3 py-1 text-xs font-extrabold text-[#2E9F86]">{{ $summaryCards[0]['change'] }}</span>
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
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Spent</span>
                            <span class="mt-1 text-xl font-extrabold text-[#B8336A]">{{ $summaryCards[0]['value'] }}</span>
                        </figcaption>
                    </figure>

                    <div class="min-w-0 space-y-3">
                        @foreach ($categories as $category)
                            <div class="flex min-w-0 flex-wrap items-start justify-between gap-2">
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-extrabold leading-5 text-[#4B2735]">{{ $category['name'] }}</p>
                                        <p class="text-xs font-semibold text-[#9B7A82]">{{ $category['amount'] }}</p>
                                    </div>
                                </div>
                                <p class="shrink-0 text-sm font-extrabold text-[#B8336A] whitespace-nowrap">{{ $category['share'] }}%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section aria-labelledby="analytics-cards-heading" class="grid grid-cols-3 gap-3 lg:col-start-2 lg:row-start-2 lg:grid-cols-1">
                <h2 id="analytics-cards-heading" class="sr-only">Analytics cards</h2>

                @foreach ($analyticsCards as $card)
                    @php
                        $toneClasses = [
                            'blue' => 'bg-[#B8336A] text-white',
                            'green' => 'bg-[#2E9F86] text-white',
                            'cyan' => 'bg-[#FFF2C8] text-[#B8336A]',
                        ][$card['tone']];
                    @endphp

                    <article class="rounded-2xl border border-white/70 {{ $toneClasses }} p-4 shadow-[0_12px_24px_rgba(9,60,93,0.07)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_32px_rgba(9,60,93,0.12)]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.12em] opacity-75">{{ $card['label'] }}</p>
                        <p class="mt-3 text-lg font-extrabold tracking-normal">{{ $card['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold opacity-75">{{ $card['caption'] }}</p>
                    </article>
                @endforeach
            </section>

            <section aria-labelledby="weekly-trend-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-start-1 lg:row-start-2">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="weekly-trend-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">{{ $trendTitle }}</h2>
                        <p class="mt-1 text-sm font-medium text-[#9B7A82]">{{ $trendCaption }}</p>
                    </div>
                    <span class="rounded-full bg-[#DDF8E8] px-3 py-1 text-xs font-extrabold text-[#2E9F86]">Healthy</span>
                </div>

                <div
                    class="relative overflow-visible rounded-2xl border border-[#F5C9D6]/80 bg-white"
                    aria-label="ApexCharts area chart placeholder for weekly trends"
                >
                    <div
                        id="apex-weekly-trends"
                        data-chart-engine="ApexCharts"
                        data-chart-type="line"
                        data-chart-payload='@json($apexCharts['weeklyExpenseTrends'])'
                        class="h-48"
                    ></div>

                </div>
            </section>

            <section aria-labelledby="monthly-chart-heading" class="rounded-2xl border border-white bg-white/84 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-start-1 lg:row-start-3">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="monthly-chart-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">{{ $incomeExpenseTitle }}</h2>
                        <p class="mt-1 text-sm font-medium text-[#9B7A82]">{{ $incomeExpenseCaption }}</p>
                    </div>
                    <span class="rounded-full bg-[#FFF2C8] px-3 py-1 text-xs font-extrabold text-[#B8336A]">{{ $activePeriod }}</span>
                </div>

                <div
                    id="apex-monthly-income-expense"
                    data-chart-engine="ApexCharts"
                    data-chart-type="bar"
                    data-chart-payload='@json($apexCharts['monthlyIncomeVsExpense'])'
                    class="h-52 rounded-2xl bg-white"
                    aria-label="ApexCharts bar chart for monthly income versus expense"
                ></div>
            </section>

            <section aria-labelledby="category-stats-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-start-2 lg:row-start-3">
                <h2 id="category-stats-heading" class="text-xl font-bold tracking-normal text-[#4B2735]">Category Statistics</h2>

                <div class="mt-5 space-y-5">
                    @foreach ($categories as $category)
                        <article>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <p class="text-sm font-extrabold text-[#4B2735]">{{ $category['name'] }}</p>
                                <p class="text-sm font-bold text-[#9B7A82]">{{ $category['amount'] }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-[#FFF2C8]">
                                <div class="h-full rounded-full" style="width: {{ $category['share'] }}%; background-color: {{ $category['color'] }}"></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="monthly-overview-heading" class="rounded-2xl border border-white/80 bg-white/72 p-5 shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.12)] lg:col-start-1 lg:row-start-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Timeline</p>
                        <h2 id="monthly-overview-heading" class="mt-1 text-xl font-bold tracking-normal text-[#4B2735]">{{ $overviewTitle }}</h2>
                        <p class="mt-1 text-sm font-medium leading-5 text-[#9B7A82]">{{ $overviewCaption }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-[#FFF2C8] px-3 py-1 text-xs font-extrabold text-[#B8336A]">{{ count($months) }} rows</span>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($months as $month)
                        @php
                            $incomeValue = (int) preg_replace('/[^\d]/', '', $month['income']);
                            $expenseValue = (int) preg_replace('/[^\d]/', '', $month['expense']);
                            $maxValue = max($incomeValue, $expenseValue, 1);
                            $incomeWidth = max(8, min(100, round(($incomeValue / $maxValue) * 100)));
                            $expenseWidth = max(8, min(100, round(($expenseValue / $maxValue) * 100)));
                            $isPositiveNet = str_starts_with($month['net'], '+');
                        @endphp

                        <article class="rounded-2xl border border-[#F8D9E3] bg-white p-4 shadow-[0_10px_24px_rgba(9,60,93,0.05)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_32px_rgba(9,60,93,0.1)]">
                            <div class="mb-4 flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Period</p>
                                    <h3 class="mt-1 text-lg font-extrabold text-[#B8336A]">{{ $month['label'] }}</h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Net</p>
                                    <p class="mt-1 text-base font-extrabold {{ $isPositiveNet ? 'text-[#2E9F86]' : 'text-[#D93662]' }}">{{ $month['net'] }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#9B7A82]">Income</span>
                                        <span class="text-sm font-extrabold text-[#2E9F86]">{{ $month['income'] }}</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-[#DDF8E8]">
                                        <div class="h-full rounded-full bg-[#2E9F86]" style="width: {{ $incomeWidth }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold uppercase tracking-[0.12em] text-[#9B7A82]">Expense</span>
                                        <span class="text-sm font-extrabold text-[#D93662]">{{ $month['expense'] }}</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-[#FFE4EF]">
                                        <div class="h-full rounded-full bg-[#D93662]" style="width: {{ $expenseWidth }}%"></div>
                                    </div>
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
