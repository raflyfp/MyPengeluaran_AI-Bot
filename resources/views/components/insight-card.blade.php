@props([
    'title' => 'Smart Insight',
    'highlight' => '15% more',
])

<article class="w-full min-w-0 max-w-full rounded-2xl border border-[#D9E5EA] bg-white/48 p-5 shadow-[0_14px_34px_rgba(9,60,93,0.06)] backdrop-blur-xl">
    <div class="flex min-w-0 items-start gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#7EC7E8] text-white shadow-[0_10px_22px_rgba(126,199,232,0.18)]">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9 18h6M10 21h4M8.4 14.7A6 6 0 1 1 15.6 14c-.7.5-1.1 1.4-1.1 2.2v.3h-5v-.3c0-.6-.4-1.2-1.1-1.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <h2 class="truncate text-xs font-bold uppercase tracking-[0.14em] text-[#181C1E]">{{ $title }}</h2>
            <p class="mt-2 break-words text-base leading-7 text-[#243135]">
                {{ $slot }}
            </p>
        </div>
    </div>
</article>
