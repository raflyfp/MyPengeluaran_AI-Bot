@props([
    'label' => 'Total Balance',
    'amount' => 'Rp 24.562.000',
    'change' => '+2.4%',
    'caption' => 'vs bulan lalu',
])

<article
    x-data="{ visible: true }"
    class="relative w-full min-w-0 max-w-full overflow-hidden rounded-2xl bg-gradient-to-br from-[#16B69C] via-[#087B69] to-[#093C5D] p-6 text-white shadow-[0_22px_44px_rgba(9,60,93,0.22)] transition duration-200 active:scale-[0.99]"
>
    <div class="absolute -right-14 -top-16 h-40 w-40 rounded-full bg-white/14 blur-2xl"></div>
    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-[#031E2F]/35 to-transparent"></div>

    <div class="relative min-w-0">
        <div class="mb-3 flex min-w-0 items-center justify-between gap-3">
            <h2 class="min-w-0 truncate text-xs font-bold uppercase tracking-[0.14em] text-white/78">{{ $label }}</h2>
            <button
                type="button"
                class="shrink-0 rounded-full p-2 text-white/80 transition hover:bg-white/10 active:scale-95"
                :aria-label="visible ? 'Hide balance' : 'Show balance'"
                @click="visible = ! visible"
            >
                <svg x-show="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="2"/>
                </svg>
                <svg x-show="! visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="m4 4 16 16M10.6 10.6A3 3 0 0 0 12 15a3 3 0 0 0 2.8-4.1M7.5 7.8C4.4 9.5 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.9-.4 4.1-1M19.1 15.1c1.6-1.4 2.4-3.1 2.4-3.1S18 6 12 6c-.8 0-1.6.1-2.3.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <p class="mb-5 max-w-full truncate text-[2.35rem] font-extrabold leading-none tracking-normal sm:text-5xl">
            <span x-show="visible">{{ $amount }}</span>
            <span x-show="! visible">Rp ********</span>
        </p>

        <div class="flex min-w-0 items-center gap-3 text-sm font-semibold">
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-white/20 px-3 py-1.5 text-white shadow-inner backdrop-blur">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 19V5M6 11l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ $change }}
            </span>
            <span class="min-w-0 truncate text-white/78">{{ $caption }}</span>
        </div>
    </div>
</article>
