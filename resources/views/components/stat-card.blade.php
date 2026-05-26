@props([
    'type' => 'income',
    'label' => 'Income',
    'amount' => 'Rp 0',
])

@php
    $isIncome = $type === 'income';
    $iconClasses = $isIncome
        ? 'bg-[#C9F3E7] text-[#007A53]'
        : 'bg-[#FFE7E7] text-[#BA1A1A]';
@endphp

<article class="flex items-center gap-4 rounded-2xl border border-white/80 bg-white/72 p-4 shadow-[0_18px_36px_rgba(9,60,93,0.08)] backdrop-blur-2xl transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(9,60,93,0.11)] active:scale-[0.98]">
    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $iconClasses }}">
        @if ($isIncome)
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M19 5 5 19M5 19h10M5 19V9" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @else
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 19 19 5M19 5H9M19 5v10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        @endif
    </div>

    <div class="min-w-0">
        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#485A60]">{{ $label }}</p>
        <p class="mt-0.5 truncate text-xl font-extrabold tracking-normal text-[#181C1E]">{{ $amount }}</p>
    </div>
</article>
