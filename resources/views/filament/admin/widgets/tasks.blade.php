<nav aria-label="Common tasks" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ($links as $link)
        <a
            href="{{ $link['url'] }}"
            class="flex min-h-11 flex-col justify-center rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-950/5 transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700 dark:bg-gray-900 dark:ring-white/10 dark:hover:bg-white/5 dark:focus-visible:outline-amber-300"
        >
            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $link['label'] }}</span>
            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $link['hint'] }}</span>
        </a>
    @endforeach
</nav>
