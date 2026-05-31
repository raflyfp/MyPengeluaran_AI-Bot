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
        class="mx-auto min-h-screen max-w-[430px] pb-32 lg:ml-72 lg:mr-0 lg:max-w-none lg:pb-12"
        x-data="{
            editing: null,
            detail: null,
            confirmEditSave: false,
            confirmDelete: false,
            editAction: '',
            deleteAction: '',
            pendingDeleteAction: '',
            categories: @js($categoriesForEdit),
            openEdit(transaction) {
                this.$dispatch('bottom-nav-visibility', { hidden: true });
                this.editing = {...transaction};
                this.editing.category_id = String(transaction.category_id);
                this.editAction = `/transactions/${transaction.id}`;
            },
            closeEdit() {
                this.editing = null;
                this.editAction = '';
                this.$dispatch('bottom-nav-visibility', { hidden: false });
            },
            openDetail(transaction) {
                this.detail = {...transaction};
                this.deleteAction = `/transactions/${transaction.id}`;
                this.$dispatch('bottom-nav-visibility', { hidden: true });
            },
            closeDetail() {
                this.detail = null;
                this.deleteAction = '';
                this.$dispatch('bottom-nav-visibility', { hidden: false });
            },
            openConfirmSave() {
                this.confirmEditSave = true;
                this.$dispatch('bottom-nav-visibility', { hidden: true });
            },
            closeConfirmSave() {
                this.confirmEditSave = false;
                this.$dispatch('bottom-nav-visibility', { hidden: Boolean(this.editing || this.detail || this.confirmDelete) });
            },
            openConfirmDelete(action) {
                this.pendingDeleteAction = action;
                this.confirmDelete = true;
                this.$dispatch('bottom-nav-visibility', { hidden: true });
            },
            closeConfirmDelete() {
                this.confirmDelete = false;
                this.pendingDeleteAction = '';
                this.$dispatch('bottom-nav-visibility', { hidden: Boolean(this.editing || this.detail || this.confirmEditSave) });
            },
        }"
        @keydown.escape.window="
            if (confirmDelete) {
                closeConfirmDelete();
            } else if (confirmEditSave) {
                closeConfirmSave();
            } else if (editing) {
                closeEdit();
            } else if (detail) {
                closeDetail();
            }
        "
    >
        <header class="fixed inset-x-0 top-0 z-40 mx-auto max-w-[430px] border-b border-white/70 bg-[#FFF7EA]/78 px-5 py-4 shadow-[0_2px_16px_rgba(9,60,93,0.05)] backdrop-blur-2xl lg:left-72 lg:right-6 lg:top-6 lg:mx-0 lg:max-w-none lg:rounded-[1.75rem] lg:border lg:px-8">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9A6275]">MyPengeluaran</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-normal text-[#B8336A]">Transactions</h1>
                </div>

                <button
                    type="button"
                    aria-label="Add transaction"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-white/76 text-[#B8336A] shadow-[0_10px_24px_rgba(9,60,93,0.08)] backdrop-blur-xl transition duration-200 hover:bg-white active:scale-95"
                    @click="$dispatch('open-add-transaction')"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </header>

        <main class="space-y-7 px-5 pt-28 lg:max-w-7xl lg:px-8 lg:pt-36">
            @if (session('status'))
                <div class="rounded-2xl border border-[#BCEFD1] bg-[#DDF8E8] px-4 py-3 text-sm font-bold text-[#2E9F86] shadow-[0_10px_24px_rgba(9,60,93,0.06)]">
                    {{ session('status') }}
                </div>
            @endif

            <section aria-labelledby="transaction-search-heading" class="space-y-4">
                <h2 id="transaction-search-heading" class="sr-only">Search and filter transactions</h2>

                <form method="GET" action="{{ route('transactions.index') }}" class="space-y-4">
                    @if ($activeType)
                        <input type="hidden" name="type" value="{{ $activeType }}">
                    @endif

                    <label class="flex h-14 items-center gap-3 rounded-full border border-white/80 bg-white/78 px-5 text-[#9A6275] shadow-[0_14px_34px_rgba(9,60,93,0.08)] backdrop-blur-2xl focus-within:ring-2 focus-within:ring-[#7EC7E8]/70">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m21 21-4.3-4.3M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <input
                            name="search"
                            value="{{ $searchValue }}"
                            type="search"
                            placeholder="Search transactions"
                            class="w-full border-0 bg-transparent p-0 text-base font-semibold text-[#4B2735] placeholder:text-[#9B7A82] focus:ring-0"
                        >
                    </label>
                </form>

                <div class="-mx-5 flex gap-3 overflow-x-auto px-5 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="list" aria-label="Transaction filters">
                    @foreach ($chips as $chip)
                        <a
                            href="{{ $filterUrl($chip['key']) }}"
                            class="shrink-0 rounded-full px-5 py-2.5 text-sm font-extrabold transition duration-200 active:scale-95 {{ $activeType === $chip['key'] ? 'bg-[#B8336A] text-white shadow-[0_10px_24px_rgba(9,60,93,0.2)]' : 'bg-white/70 text-[#684C59] shadow-[0_8px_20px_rgba(9,60,93,0.06)] hover:bg-white' }}"
                        >
                            {{ $chip['label'] }}
                        </a>
                    @endforeach
                </div>
            </section>

            <section aria-labelledby="transaction-summary-heading" class="grid grid-cols-2 gap-4 lg:max-w-3xl">
                <h2 id="transaction-summary-heading" class="sr-only">Transaction summary</h2>

                <article class="rounded-2xl border border-white/80 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Spent</p>
                    <p class="mt-2 text-xl font-extrabold tracking-normal text-[#D93662]">{{ $summary['formatted_monthly_expense_total'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-[#9B7A82]">{{ $summary['expense_count'] }} expenses this month</p>
                </article>

                <article class="rounded-2xl border border-white/80 bg-white/68 p-4 shadow-[0_16px_34px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Income</p>
                    <p class="mt-2 text-xl font-extrabold tracking-normal text-[#2E9F86]">{{ $summary['formatted_monthly_income_total'] }}</p>
                    <p class="mt-1 text-xs font-semibold text-[#9B7A82]">{{ $summary['income_count'] }} deposits this month</p>
                </article>
            </section>

            <section id="transactions" aria-labelledby="transaction-list-heading" class="space-y-6">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">History</p>
                        <h2 id="transaction-list-heading" class="mt-1 text-xl font-bold tracking-normal text-[#4B2735]">Recent Transactions</h2>
                    </div>
                    <span class="rounded-full bg-[#FFF2C8] px-3 py-1 text-xs font-extrabold text-[#2E9F86]">Live</span>
                </div>

                @forelse ($transactionGroups as $group)
                    <section class="space-y-3" aria-labelledby="group-{{ $loop->index }}">
                        <div class="flex items-center justify-between px-1">
                            <h3 id="group-{{ $loop->index }}" class="text-sm font-extrabold uppercase tracking-[0.12em] text-[#9A6275]">{{ $group['date'] }}</h3>
                            <p class="text-sm font-extrabold {{ str_starts_with($group['summary'], '+') ? 'text-[#2E9F86]' : 'text-[#D93662]' }}">{{ $group['summary'] }}</p>
                        </div>

                        <div class="space-y-4">
                            @foreach ($group['items'] as $transaction)
                                <button
                                    type="button"
                                    class="w-full text-left"
                                    @click="openDetail(@js($transaction))"
                                    aria-label="Open transaction details"
                                >
                                    <x-transaction-card
                                        :title="$transaction['title']"
                                        :category="$transaction['category']"
                                        :time="$transaction['time']"
                                        :amount="$transaction['amount']"
                                        :type="$transaction['type']"
                                        :icon="$transaction['icon']"
                                    />
                                </button>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <article class="rounded-2xl border border-white/80 bg-white/76 p-6 text-center shadow-[0_18px_38px_rgba(9,60,93,0.08)] backdrop-blur-xl">
                        <p class="text-lg font-extrabold text-[#B8336A]">No transactions yet</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-[#9B7A82]">Add your first expense manually or send one through Telegram.</p>
                        <button
                            type="button"
                            class="mt-5 inline-flex items-center justify-center rounded-full bg-[#B8336A] px-5 py-3 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#F26D99] active:scale-[0.98]"
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
            class="fixed inset-0 z-[1000] flex items-end justify-center bg-[#5C1632]/42 px-4 pb-4 backdrop-blur-sm"
            @click.self="closeEdit()"
        >
            <div class="max-h-[92vh] w-full max-w-[430px] overflow-y-auto rounded-[1.75rem] border border-white/80 bg-white p-5 shadow-[0_24px_54px_rgba(9,60,93,0.24)] lg:max-w-xl" x-show="editing" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-y-8 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Update</p>
                        <h2 class="mt-1 text-xl font-extrabold text-[#B8336A]">Edit Transaction</h2>
                    </div>
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FFF1F6] text-[#B8336A] transition active:scale-95" @click="closeEdit()" aria-label="Close edit form">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="space-y-4" x-ref="editForm">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="source" :value="editing?.source || 'manual'">

                    <div class="grid grid-cols-2 gap-3">
                        <label class="rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-3">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Type</span>
                            <select name="type" x-model="editing.type" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#B8336A] focus:ring-0">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </label>

                        <label class="rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-3">
                            <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Amount</span>
                            <input name="amount" type="number" min="0.01" step="0.01" x-model="editing.raw_amount" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#B8336A] focus:ring-0">
                        </label>
                    </div>

                    <label class="block rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Category</span>
                        <select name="category_id" x-model="editing.category_id" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#B8336A] focus:ring-0">
                            <template x-for="category in categories.filter((category) => category.type === editing.type)" :key="category.id">
                                <option :value="String(category.id)" x-text="category.name"></option>
                            </template>
                        </select>
                    </label>

                    <label class="block rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Date</span>
                        <input name="transaction_date" type="datetime-local" x-model="editing.transaction_date" class="mt-2 w-full border-0 bg-transparent p-0 text-sm font-extrabold text-[#B8336A] focus:ring-0">
                    </label>

                    <label class="block rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-3">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Note</span>
                        <textarea name="note" rows="3" x-model="editing.note" class="mt-2 w-full resize-none border-0 bg-transparent p-0 text-sm font-semibold text-[#4B2735] placeholder:text-[#9B7A82] focus:ring-0"></textarea>
                    </label>

                    <button type="button" class="flex w-full items-center justify-center rounded-full bg-[#B8336A] px-5 py-3.5 text-sm font-extrabold text-white shadow-[0_14px_28px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#F26D99] active:scale-[0.98]" @click="openConfirmSave()">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div
            x-cloak
            x-show="confirmEditSave"
            x-transition.opacity
            class="fixed inset-0 z-[1100] flex items-end justify-center bg-[#5C1632]/42 px-4 pb-4 backdrop-blur-sm"
            @click.self="closeConfirmSave()"
        >
            <div class="w-full max-w-[380px] overflow-hidden rounded-[1.5rem] border border-white/80 bg-white shadow-[0_20px_46px_rgba(9,60,93,0.22)]" x-show="confirmEditSave" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                <div class="px-5 py-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Confirm</p>
                    <h3 class="mt-2 text-lg font-extrabold text-[#B8336A]">Save changes?</h3>
                    <p class="mt-2 text-sm font-semibold text-[#9A6275]">Pastikan detail transaksi sudah benar sebelum disimpan.</p>
                </div>
                <div class="flex items-center gap-3 border-t border-[#F8D9E3] bg-[#FFF7EA] px-5 py-4">
                    <button type="button" class="flex-1 rounded-full border border-[#F5C9D6] bg-white px-4 py-2.5 text-sm font-extrabold text-[#B8336A] transition duration-200 hover:bg-[#EEF4F7]" @click="closeConfirmSave()">Cancel</button>
                    <button type="button" class="flex-1 rounded-full bg-[#B8336A] px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(9,60,93,0.18)] transition duration-200 hover:bg-[#F26D99]" @click="closeConfirmSave(); $refs.editForm.submit();">Yes, Save</button>
                </div>
            </div>
        </div>

        <div
            x-cloak
            x-show="confirmDelete"
            x-transition.opacity
            class="fixed inset-0 z-[1100] flex items-end justify-center bg-[#5C1632]/42 px-4 pb-4 backdrop-blur-sm"
            @click.self="closeConfirmDelete()"
        >
            <div class="w-full max-w-[380px] overflow-hidden rounded-[1.5rem] border border-white/80 bg-white shadow-[0_20px_46px_rgba(9,60,93,0.22)]" x-show="confirmDelete" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                <div class="px-5 py-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Confirm</p>
                    <h3 class="mt-2 text-lg font-extrabold text-[#B8336A]">Delete transaction?</h3>
                    <p class="mt-2 text-sm font-semibold text-[#9A6275]">Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="flex items-center gap-3 border-t border-[#F8D9E3] bg-[#FFF7EA] px-5 py-4">
                    <button type="button" class="flex-1 rounded-full border border-[#F5C9D6] bg-white px-4 py-2.5 text-sm font-extrabold text-[#B8336A] transition duration-200 hover:bg-[#EEF4F7]" @click="closeConfirmDelete()">Cancel</button>
                    <form method="POST" :action="pendingDeleteAction" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full rounded-full bg-[#D93662] px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_10px_22px_rgba(186,26,26,0.25)] transition duration-200 hover:bg-[#C92752]">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>

        <div
            x-cloak
            x-show="detail"
            x-transition.opacity
            class="fixed inset-0 z-[1000] flex items-end justify-center bg-[#5C1632]/42 px-4 pb-4 pt-4 backdrop-blur-sm"
            @click.self="closeDetail()"
        >
            <div class="max-h-[92vh] w-full max-w-[430px] overflow-y-auto rounded-[1.75rem] border border-white/80 bg-white shadow-[0_24px_54px_rgba(9,60,93,0.24)] lg:max-w-xl" x-show="detail" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-y-8 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
                <div class="flex items-center justify-between border-b border-[#F8D9E3] px-5 py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#9B7A82]">Detail</p>
                        <h2 class="mt-1 text-xl font-extrabold text-[#B8336A]" x-text="detail?.title"></h2>
                    </div>
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FFF1F6] text-[#B8336A] transition active:scale-95" @click="closeDetail()" aria-label="Close transaction detail">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 px-5 py-5">
                    <div class="rounded-2xl border border-[#F8D9E3] bg-[#FFF7EA] p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Category</p>
                                <p class="mt-2 text-lg font-extrabold text-[#B8336A]" x-text="detail?.category"></p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-extrabold" :class="detail?.type === 'income' ? 'bg-[#DDF8E8] text-[#2E9F86]' : 'bg-[#FFE4EF] text-[#D93662]'" x-text="detail?.type === 'income' ? 'Income' : 'Expense'"></span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Amount</p>
                                <p class="mt-2 text-lg font-extrabold" :class="detail?.type === 'income' ? 'text-[#2E9F86]' : 'text-[#D93662]'" x-text="detail?.amount"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Date</p>
                                <p class="mt-2 text-sm font-semibold text-[#9A6275]" x-text="detail?.date_full"></p>
                                <p class="mt-1 text-xs font-semibold text-[#9B7A82]" x-text="detail?.time"></p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[#F8D9E3] bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#9B7A82]">Note</p>
                        <p class="mt-2 text-sm font-semibold text-[#4B2735]" x-text="detail?.note || 'No notes added.'"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-full bg-[#B8336A] px-4 py-3 text-sm font-extrabold text-white shadow-[0_12px_24px_rgba(9,60,93,0.22)] transition duration-200 hover:bg-[#F26D99] active:scale-[0.98]"
                            @click="const selected = detail; closeDetail(); openEdit(selected);"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m14.5 5.5 4 4M4 20h4l10.5-10.5a2.8 2.8 0 0 0-4-4L4 16v4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Edit
                        </button>

                        <form method="POST" :action="deleteAction" @submit.prevent="openConfirmDelete(deleteAction)">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-full border border-[#F7A9C2] bg-[#FFE4EF] px-4 py-3 text-sm font-extrabold text-[#D93662] transition duration-200 hover:bg-[#FFD2E0] active:scale-[0.98]"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-bottom-nav active="transactions" />
</x-app-layout>
