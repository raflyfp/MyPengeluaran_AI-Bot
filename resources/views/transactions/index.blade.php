<x-app-layout>
    @php
        $activeType = $filters['type'] ?? '';
        $searchValue = $filters['search'] ?? '';
        $chips = [
            ['key' => '', 'label' => 'All'],
            ['key' => 'expense', 'label' => 'Expense'],
            ['key' => 'income', 'label' => 'Income'],
        ];
        $filterUrl = fn (string $type) => route('transactions.index', array_filter([
            'type' => $type ?: null,
            'search' => $searchValue ?: null,
        ]));
        $categoriesForEdit = $categories
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ])
            ->values();
    @endphp

    <div
        class="mx-auto min-h-screen max-w-[430px] pb-32"
        x-data="{
            editing: null,
            editAction: '',
            categories: @js($categoriesForEdit),
            openEdit(transaction) {
                this.editing = {...transaction};
                this.editing.category_id = String(transaction.category_id);
                this.editAction = `/transactions/${transaction.id}`;
            },
            closeEdit() {
                this.editing = null;
                this.editAction = '';
            },
        }"
    >
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#F7FAFC]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#485A60]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Transactions</h1>
                </div>

                <button
                    type="button"
                    aria-label="Add transaction"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#093C5D] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                    @click="$dispatch('open-add-transaction')"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="space-y-7 px-5 pt-28">
            @if (session('status'))
                <div class="rounded-2xl border border-[#BFEDE5] bg-[#DFF8F4] px-4 py-3 text-sm font-bold text-[#007A53] shadow-[0_10px_24px_rgba(9,60,93,0.06)]">
                    {{ session('status') }}
                </div>
            @endif

            <section aria-labelledby="transaction-search-heading" class="space-y-4">
                <h2 id="transaction-search-heading" class="sr-only">Search and filter transactions</h2>

                <form method="GET" action="{{ route('transactions.index') }}" class="space-y-4">
                    @if ($activeType)
                        <input type="hidden" name="type" value="{{ $activeType }}">
                    @endif

                    <label class="flex h-14 items-center gap-3 rounded-full border border-white/80 bg-white/78 px-5 text-[#485A60] shadow-[0_14px_34px_rgba(9,60,93,0.08)] backdrop-blur-2xl focus-within:ring-2 focus-within:ring-[#6FD1D7]/70">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 21-4.3-4.3M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input
                            name="search"
                            value="{{ $searchValue }}"
                            type="search"
                            placeholder="Search transactions"
                            class="w-full border-0 bg-transparent p-0 text-base font-semibold text-[#181C1E] placeholder:text-[#72777E] focus:ring-0"
                        >
                    </label>
                </form>

                <div class="-mx-5 flex gap-3 overflow-x-auto px-5 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="list" aria-label="Transaction filters">
                    @foreach ($chips as $chip)
                        <a
                            href="{{ $filterUrl($chip['key']) }}"
                            class="shrink-0 rounded-full px-5 py-2.5 text-sm font-extrabold transition duration-200 active:scale-95 {{ $activeType === $chip['key'] ? 'bg-[#093C5D] text-white shadow-[0_10px_24px_rgba(9,60,93,0.2)]' : 'bg-white/70 text-[#3C4A42] shadow-[0_8px_20px_rgba(9,60,93,0.06)] hover:bg-white' }}"
                        >
                            {{ $chip['label'] }}
                        </a>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="transaction-summary-heading" class="grid grid-cols-2 gap-4">
                <h2 id="transaction-summary-heading" class="sr-only">Transaction summary</h2>

                <article class="rounded-2xl border border-white/80 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Spent</p>
                    <p class="mt-2 text-xl font-extrabold tracking-normal text-[#BA1A1A]">{{ $summary['formatted_monthly_expense_total'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-[#72777E]">{{ $summary['expense_count'] }} expenses this month</p>
                </article>

                <article class="rounded-2xl border border-white/80 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Income</p>
                    <p class="mt-2 text-xl font-extrabold tracking-normal text-[#007A53]">{{ $summary['formatted_monthly_income_total'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-[#72777E]">{{ $summary['income_count'] }} deposits this month</p>
                </article>
            </section>

            <section id="transactions" aria-labelledby="transaction-list-heading" class="space-y-6">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">History</p>
                        <h2 id="transaction-list-heading" class="mt-1 text-xl font-bold tracking-normal text-[#181C1E]">Recent Transactions</h2>
                    </div>
                    <span class="rounded-full bg-[#EAF7F8] px-3 py-1 text-xs font-extrabold text-[#007A53]">Live</span>
                </div>

                @forelse ($transactionGroups as $group)
                    <section class="space-y-3" aria-labelledby="group-{{ $loop->index }}">
                        <div class="flex items-center justify-between px-1">
                            <h3 id="group-{{ $loop->index }}" class="text-sm font-extrabold uppercase tracking-[0.12em] text-[#485A60]">{{ $group['date'] }}</h3>
                            <p class="text-sm font-extrabold {{ str_starts_with($group['summary'], '+') ? 'text-[#007A53]' : 'text-[#BA1A1A]' }}">{{ $group['summary'] }}</p>
                        </div>

                        <div class="space-y-4">
                            @foreach ($group['items'] as $transaction)
                                <div
                                    class="relative overflow-hidden rounded-2xl"
                                    x-data="{ open: false, startX: 0 }"
                                    @touchstart.passive="startX = $event.changedTouches[0].clientX"
                                    @touchend.passive="
                                        const delta = $event.changedTouches[0].clientX - startX;
                                        if (delta < -36) open = true;
                                        if (delta > 36) open = false;
                                    "
                                >
                                    <div class="absolute inset-y-0 right-0 flex items-center gap-2 rounded-2xl bg-gradient-to-l from-[#BA1A1A] to-[#0D5DCF] px-3 text-white">
                                        <button
                                            type="button"
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white/16 transition hover:bg-white/24 active:scale-95"
                                            aria-label="Edit transaction"
                                            @click="openEdit(@js($transaction)); open = false"
                                        >
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="m14.5 5.5 4 4M4 20h4l10.5-10.5a2.8 2.8 0 0 0-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        <form method="POST" action="{{ route('transactions.destroy', $transaction['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/16 transition hover:bg-white/24 active:scale-95" aria-label="Delete transaction">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    <div
                                        class="relative transition duration-300 ease-out"
                                        :class="open ? '-translate-x-24' : 'translate-x-0'"
                                        @click.outside="open = false"
                                    >
                                        <x-transaction-card
                                            :title="$transaction['title']"
                                            :category="$transaction['category']"
                                            :time="$transaction['time']"
                                            :amount="$transaction['amount']"
                                            :type="$transaction['type']"
                                            :icon="$transaction['icon']"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <article class="rounded-2xl border border-white/80 bg-white/76 p-6 text-center shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <p class="text-lg font-extrabold text-[#093C5D]">No transactions yet</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#72777E]">Add your first expense manually or send one through Telegram.</p>
                        <button
                            type="button"
                            class="mt-5 inline-flex items-center justify-center rounded-full bg-[#093C5D] px-5 py-3 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]"
                            @click="$dispatch('open-add-transaction')"
                        >
                            Add Transaction
                        </button>
                    </article>
                @endforelse

                @if ($transactions->hasPages())
                    <div class="rounded-2xl border border-white/80 bg-white/72 p-3 shadow-[0_14px_30px_rgba(9,60,93,0.07)] backdrop-blur-xl">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </section>
        </main>

        <div
            x-cloak
            x-show="editing"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-end justify-center bg-[#061E2E]/42 px-4 pb-4 backdrop-blur-sm"
            @keydown.escape.window="closeEdit()"
        >
            <div class="w-full max-w-[430px] rounded-[1.75rem] border border-white/80 bg-white p-5 shadow-[0_24px_54px_rgba(9,60,93,0.24)]" x-show="editing" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-y-8 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Update</p>
                        <h2 class="mt-1 text-xl font-extrabold text-[#093C5D]">Edit Transaction</h2>
                    </div>
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#F2F7F8] text-[#093C5D] transition active:scale-95" @click="closeEdit()" aria-label="Close edit form">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="source" :value="editing?.source || 'manual'">

                    <div class="grid grid-cols-2 gap-3">
                        <label class="rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-3">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Type</span>
                            <select name="type" x-model="editing.type" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#093C5D] focus:ring-0">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </label>

                        <label class="rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-3">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Amount</span>
                            <input name="amount" type="number" min="0.01" step="0.01" x-model="editing.raw_amount" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#093C5D] focus:ring-0">
                        </label>
                    </div>

                    <label class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Category</span>
                        <select name="category_id" x-model="editing.category_id" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#093C5D] focus:ring-0">
                            <template x-for="category in categories.filter((category) => category.type === editing.type)" :key="category.id">
                                <option :value="String(category.id)" x-text="category.name"></option>
                            </template>
                        </select>
                    </label>

                    <label class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Date</span>
                        <input name="transaction_date" type="datetime-local" x-model="editing.transaction_date" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#093C5D] focus:ring-0">
                    </label>

                    <label class="block rounded-2xl border border-[#E7EEF2] bg-[#F7FAFC] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Note</span>
                        <textarea name="note" rows="3" x-model="editing.note" class="mt-2 w-full resize-none border-0 bg-transparent p-0 text-sm font-semibold text-[#181C1E] placeholder:text-[#72777E] focus:ring-0"></textarea>
                    </label>

                    <button type="submit" class="flex w-full items-center justify-center rounded-full bg-[#093C5D] px-5 py-3.5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#0C6680] active:scale-[0.98]">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-bottom-nav active="transactions" />
</x-app-layout>
