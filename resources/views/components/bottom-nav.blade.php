@props(['active' => 'home'])

@php
    $items = [
        ['key' => 'home', 'label' => 'Home', 'href' => route('dashboard')],
        ['key' => 'transactions', 'label' => 'Transactions', 'href' => route('transactions.index')],
        ['key' => 'analytics', 'label' => 'Analytics', 'href' => route('analytics.index')],
        ['key' => 'profile', 'label' => 'Profile', 'href' => route('profile.index')],
    ];
@endphp

<nav
    aria-label="Primary navigation"
    x-data="{ active: @js($active) }"
    class="fixed inset-x-0 bottom-4 z-50 mx-auto flex w-[calc(100%-32px)] max-w-[398px] items-center justify-between rounded-full border border-white/70 bg-white/75 px-4 py-2 shadow-[0_18px_45px_rgba(9,60,93,0.16)] backdrop-blur-2xl"
>
    @foreach ($items as $index => $item)
        @if ($index === 2)
            <button
                type="button"
                aria-label="Add transaction"
                @click="$dispatch('open-add-transaction')"
                class="-mt-10 flex h-16 w-16 items-center justify-center rounded-full border-[5px] border-[#F7FAFC] bg-gradient-to-br from-[#6FD1D7] to-[#008C88] text-white shadow-[0_18px_36px_rgba(0,140,136,0.32)] transition duration-200 hover:scale-105 active:scale-95"
            >
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                </svg>
            </button>
        @endif

        <a
            href="{{ $item['href'] }}"
            aria-label="{{ $item['label'] }}"
            @click="active = @js($item['key'])"
            class="flex h-12 w-12 items-center justify-center rounded-full transition duration-200 active:scale-90"
            :class="active === @js($item['key'])
                ? 'bg-[#007A53] text-white shadow-[0_10px_22px_rgba(0,122,83,0.28)]'
                : 'text-[#3A4A4D] hover:bg-[#EAF3F6]'"
        >
            @switch($item['key'])
                @case('home')
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 10.8 12 4l8 6.8V20a1 1 0 0 1-1 1h-4.5v-6h-5v6H5a1 1 0 0 1-1-1v-9.2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                    @break
                @case('transactions')
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3h10v18l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2L5 21V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M9 8h6M9 12h6M9 16h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    @break
                @case('analytics')
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 20V8M12 20V4M19 20v-9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M3 20h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    @break
                @default
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4.5 21a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
            @endswitch
        </a>
    @endforeach
</nav>
