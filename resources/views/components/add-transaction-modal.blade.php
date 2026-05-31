@php
    use App\Models\Category;

    $categories = Category::query()
        ->orderBy('type')
        ->orderBy('name')
        ->get(['id', 'name', 'type']);

    $incomeCategories = $categories->where('type', 'income');
    $expenseCategories = $categories->where('type', 'expense');
    $transactionErrorFields = ['type', 'amount', 'category_id', 'note', 'transaction_date', 'source'];
    $hasTransactionErrors = collect($transactionErrorFields)->contains(fn ($field) => $errors->has($field));
@endphp

<section
    x-data="{
        open: @js($hasTransactionErrors),
        type: @js(old('type', 'expense')),
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        show() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => this.$refs.amount?.focus());
        }
    }"
    x-on:open-add-transaction.window="show()"
    x-on:keydown.escape.window="close()"
    x-effect="open ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[70] bg-[#031E2F]/45 backdrop-blur-sm"
        aria-hidden="true"
        @click="close()"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-y-full opacity-80"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-220"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-80"
        role="dialog"
        aria-modal="true"
        aria-labelledby="add-transaction-title"
        class="fixed inset-x-0 bottom-0 z-[80] mx-auto max-w-[430px] rounded-t-[2rem] border border-white/70 bg-white/92 px-5 pb-6 pt-3 shadow-[0_-24px_60px_rgba(9,60,93,0.22)] backdrop-blur-2xl lg:bottom-8 lg:max-w-xl lg:rounded-[2rem]"
    >
        <div class="mx-auto mb-5 h-1.5 w-12 rounded-full bg-[#D8E4E8]"></div>

        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#72777E]">Quick Entry</p>
                <h2 id="add-transaction-title" class="mt-1 text-2xl font-extrabold tracking-normal text-[#093C5D]">Add Transaction</h2>
            </div>

            <button
                type="button"
                aria-label="Close add transaction modal"
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#F2F7F8] text-[#093C5D] transition hover:bg-[#EAF3F6] active:scale-95"
                @click="close()"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="source" value="manual">

            <fieldset>
                <legend class="mb-2 text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Transaction Type</legend>
                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-[#F2F7F8] p-1">
                    <label class="cursor-pointer">
                        <input x-model="type" type="radio" name="type" value="expense" class="sr-only">
                        <span
                            class="flex h-11 items-center justify-center rounded-xl text-sm font-extrabold transition duration-200"
                            :class="type === 'expense' ? 'bg-[#BA1A1A] text-white shadow-sm' : 'text-[#3C4A42]'"
                        >
                            Expense
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input x-model="type" type="radio" name="type" value="income" class="sr-only">
                        <span
                            class="flex h-11 items-center justify-center rounded-xl text-sm font-extrabold transition duration-200"
                            :class="type === 'income' ? 'bg-[#007A53] text-white shadow-sm' : 'text-[#3C4A42]'"
                        >
                            Income
                        </span>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
                @enderror
            </fieldset>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Amount</span>
                <div class="flex h-14 items-center gap-3 rounded-2xl border border-[#DCE8EB] bg-[#F7FAFC] px-4 focus-within:border-[#6FD1D7] focus-within:ring-2 focus-within:ring-[#6FD1D7]/30">
                    <span class="text-sm font-extrabold text-[#093C5D]">Rp</span>
                    <input
                        x-ref="amount"
                        type="number"
                        name="amount"
                        value="{{ old('amount') }}"
                        min="0.01"
                        step="0.01"
                        inputmode="decimal"
                        placeholder="0"
                        class="w-full border-0 bg-transparent p-0 text-xl font-extrabold text-[#181C1E] placeholder:text-[#A5B2B7] focus:ring-0"
                    >
                </div>
                @error('amount')
                    <p class="mt-2 text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
                @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Category</span>

                <select
                    x-show="type === 'expense'"
                    :disabled="type !== 'expense'"
                    name="category_id"
                    class="h-14 w-full rounded-2xl border-[#DCE8EB] bg-[#F7FAFC] text-base font-bold text-[#181C1E] focus:border-[#6FD1D7] focus:ring-[#6FD1D7]/40"
                >
                    <option value="">Choose expense category</option>
                    @foreach ($expenseCategories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select
                    x-show="type === 'income'"
                    :disabled="type !== 'income'"
                    name="category_id"
                    class="h-14 w-full rounded-2xl border-[#DCE8EB] bg-[#F7FAFC] text-base font-bold text-[#181C1E] focus:border-[#6FD1D7] focus:ring-[#6FD1D7]/40"
                >
                    <option value="">Choose income category</option>
                    @foreach ($incomeCategories as $category)
                        <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                @if ($categories->isEmpty())
                    <p class="mt-2 text-sm font-semibold text-[#9A5C00]">No categories yet. Seed or create categories before adding transactions.</p>
                @endif

                @error('category_id')
                    <p class="mt-2 text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
                @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Transaction Date</span>
                <input
                    type="datetime-local"
                    name="transaction_date"
                    value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                    class="h-14 w-full rounded-2xl border-[#DCE8EB] bg-[#F7FAFC] text-base font-bold text-[#181C1E] focus:border-[#6FD1D7] focus:ring-[#6FD1D7]/40"
                >
                @error('transaction_date')
                    <p class="mt-2 text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
                @enderror
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-bold uppercase tracking-[0.14em] text-[#72777E]">Note</span>
                <textarea
                    name="note"
                    rows="3"
                    placeholder="Example: Kopi Kenangan after meeting"
                    class="w-full resize-none rounded-2xl border-[#DCE8EB] bg-[#F7FAFC] text-base font-semibold text-[#181C1E] placeholder:text-[#A5B2B7] focus:border-[#6FD1D7] focus:ring-[#6FD1D7]/40"
                >{{ old('note') }}</textarea>
                @error('note')
                    <p class="mt-2 text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
                @enderror
            </label>

            @error('source')
                <p class="text-sm font-semibold text-[#BA1A1A]">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="flex h-14 w-full items-center justify-center rounded-full bg-[#B8336A] text-base font-extrabold text-white shadow-[0_14px_28px_rgba(184,51,106,0.18)] transition duration-200 hover:scale-[1.01] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                @disabled($categories->isEmpty())
            >
                Save Transaction
            </button>
        </form>
    </div>
</section>
