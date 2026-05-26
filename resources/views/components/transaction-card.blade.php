@props([
    'title' => 'Transaction',
    'category' => 'General',
    'time' => 'Today',
    'amount' => 'Rp 0',
    'type' => 'expense',
    'icon' => 'shopping',
])

@php
    $iconClasses = [
        'shopping' => 'bg-[#E9EEF1] text-[#334B4A]',
        'transport' => 'bg-[#E7EEFF] text-[#0D5DCF]',
        'work' => 'bg-[#C9F3E7] text-[#007A53]',
        'food' => 'bg-[#FFF1D9] text-[#9A5C00]',
        'coffee' => 'bg-[#EAF7F8] text-[#0C7A83]',
        'bill' => 'bg-[#EEF3F7] text-[#093C5D]',
        'health' => 'bg-[#FFE7EC] text-[#BA1A1A]',
        'transfer' => 'bg-[#DFF8F4] text-[#007A53]',
        'subscription' => 'bg-[#EDEBFF] text-[#4D42A8]',
    ][$icon] ?? 'bg-[#E9EEF1] text-[#334B4A]';

    $amountClasses = $type === 'income' ? 'text-[#007A53]' : 'text-[#181C1E]';
@endphp

<article class="flex min-w-0 items-center justify-between gap-3 rounded-2xl border border-[#E7EEF2] bg-white p-4 shadow-[0_8px_22px_rgba(9,60,93,0.06)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_14px_30px_rgba(9,60,93,0.09)] active:scale-[0.985] sm:gap-4">
    <div class="flex min-w-0 items-center gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
            @switch($icon)
                @case('transport')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 13h14l-1.2-6.2A2.2 2.2 0 0 0 15.7 5H8.3a2.2 2.2 0 0 0-2.1 1.8L5 13Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M7 17h.01M17 17h.01M6 13v5M18 13v5M8 18h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('work')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 8h14v11H5V8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('food')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3v8M10 3v8M7 7h3M17 3v18M14 7c0-2.2 1.3-4 3-4v8c-1.7 0-3-1.8-3-4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('coffee')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 8h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5V8ZM17 10h1.5a2.5 2.5 0 0 1 0 5H17M7 21h10M9 3v2M13 3v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('bill')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3h10v18l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2L5 21V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 8h6M9 12h6M9 16h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('health')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 21s-7-4.4-8.8-9.1C1.9 8.4 4.3 5 7.9 5c1.7 0 3.2.9 4.1 2.2C12.9 5.9 14.4 5 16.1 5c3.6 0 6 3.4 4.7 6.9C19 16.6 12 21 12 21Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('transfer')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 7h12M15 3l4 4-4 4M17 17H5M9 13l-4 4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('subscription')
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 5h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="m10 9 5 3-5 3V9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                    @break
                @default
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 8h14l-2 9H8L7 8ZM7 8 6.3 5H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 21h.01M18 21h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>
            @endswitch
        </div>

        <div class="min-w-0">
            <h3 class="truncate text-lg font-extrabold tracking-normal text-[#181C1E]">{{ $title }}</h3>
            <p class="mt-0.5 truncate text-sm font-medium text-[#3C4A42]">{{ $category }} <span aria-hidden="true">&bull;</span> {{ $time }}</p>
        </div>
    </div>

    <div class="shrink-0 text-right">
        <p class="text-lg font-extrabold tracking-normal {{ $amountClasses }}">{{ $amount }}</p>
        <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.12em] {{ $type === 'income' ? 'text-[#007A53]' : 'text-[#BA1A1A]' }}">
            {{ $type === 'income' ? 'Income' : 'Expense' }}
        </p>
    </div>

    @isset($actions)
        <div class="shrink-0">
            {{ $actions }}
        </div>
    @endisset
</article>
