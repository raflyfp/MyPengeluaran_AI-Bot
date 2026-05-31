@props([
    'label' => 'Total Balance',
    'amount' => 'Rp 24.562.000',
    'change' => '0%',
    'caption' => 'vs bulan lalu',
])

<article
    x-data="{ visible: true }"
    class="finance-balance-card relative w-full min-w-0 max-w-full overflow-hidden rounded-2xl bg-[#B8336A] p-5 text-white shadow-[0_18px_34px_rgba(184,51,106,0.18)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_22px_42px_rgba(184,51,106,0.24)] active:scale-[0.99]"
>
    <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#FFF2C8]/18 blur-2xl"></div>
    <div class="absolute -bottom-24 left-0 h-40 w-40 rounded-full bg-[#7EC7E8]/18 blur-3xl"></div>
    <div class="absolute inset-0 opacity-[0.16]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.9) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="absolute inset-x-0 top-0 h-20 bg-white/8"></div>

    <div class="relative min-w-0">
        <div class="mb-4 flex min-w-0 items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 class="mt-3 min-w-0 truncate text-xs font-bold uppercase tracking-[0.14em] text-white/84">{{ $label }}</h2>
            </div>
            <button
                type="button"
                class="shrink-0 rounded-full bg-white/12 p-2.5 text-white/88 transition hover:bg-white/20 active:scale-95"
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

        <div class="rounded-2xl bg-white p-4 text-[#B8336A] shadow-[0_12px_26px_rgba(24,8,16,0.08)] dark:rounded-t-lg dark:bg-[#3A2A36] dark:text-[#F2C4D7]">
            <div class="flex items-end justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#B8336A]/70 dark:text-[#F2C4D7]/70">Available</p>
                    <p class="mt-2 max-w-full truncate text-[2.35rem] font-extrabold leading-none tracking-normal text-[#B8336A] dark:text-[#F2C4D7] sm:text-5xl">
                        <span x-show="visible">{{ $amount }}</span>
                        <span x-show="! visible">Rp ********</span>
                    </p>
                </div>
                <div class="hidden h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#F9E8F0] text-[#B8336A] shadow-[0_10px_24px_rgba(0,0,0,0.08)] dark:bg-[#4A3643] dark:text-[#F2C4D7] sm:flex">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 7h16v10H4V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M16 12h2M7 17V7M9 10h3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>

            <div class="mt-4 flex min-w-0 items-center justify-between gap-3 border-t border-[#B8336A]/15 pt-4 text-sm font-semibold dark:border-[#F2C4D7]/20">
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-[#B8336A]/10 px-3 py-1.5 text-[#B8336A] dark:bg-[#F2C4D7]/12 dark:text-[#F2C4D7]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 19V5M6 11l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $change }}
                </span>
                <span class="min-w-0 text-right text-xs font-bold leading-5 text-[#B8336A]/80 dark:text-[#F2C4D7]/80">{{ $caption }}</span>
            </div>
        </div>
    </div>
</article>
