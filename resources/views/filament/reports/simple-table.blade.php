<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-900 dark:border-gray-800 dark:text-gray-100 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
        <span>{{ $heading }}</span>
        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ count($rows) }} {{ count($rows) === 1 ? 'record' : 'records' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700 dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/75 dark:bg-gray-800/75">
                    @foreach ($columns as $column)
                        <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                        @foreach ($row as $cell)
                            <td class="px-5 py-3.5">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-10 text-center text-gray-500 dark:text-gray-400" colspan="{{ count($columns) }}">
                            <div class="flex flex-col items-center justify-center space-y-1">
                                <span class="text-sm font-medium">No records found</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Calculated report data will appear here once grades are entered.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
