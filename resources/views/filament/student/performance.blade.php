<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-900 dark:border-gray-800 dark:text-gray-100 bg-gray-50/50 dark:bg-gray-800/50">
        Academic Performance History
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700 dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/75 dark:bg-gray-800/75">
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Period</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">General Average</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Attendance</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Completion</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Performance Level</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">Trend</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                        <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">{{ $row->gradingPeriod->name }}</td>
                        <td class="px-5 py-3.5 font-semibold text-indigo-600 dark:text-indigo-400">
                            {{ $row->general_average !== null ? number_format($row->general_average, 2) . '%' : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            {{ $row->attendance_rate !== null ? number_format($row->attendance_rate, 1) . '%' : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            {{ $row->activity_completion_rate !== null ? number_format($row->activity_completion_rate, 1) . '%' : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $levelName = $row->performanceLevel?->name ?? 'Pending';
                                $badgeClass = match ($levelName) {
                                    'Outstanding' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
                                    'Very Satisfactory' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
                                    'Satisfactory' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
                                    'Fairly Satisfactory' => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/20',
                                    default => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                {{ $levelName }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $trendVal = $row->trend?->value ?? 'same';
                                $trendClass = match ($trendVal) {
                                    'up' => 'text-emerald-600 dark:text-emerald-400 font-semibold',
                                    'down' => 'text-rose-600 dark:text-rose-400 font-semibold',
                                    default => 'text-gray-500 dark:text-gray-400',
                                };
                                $trendSymbol = match ($trendVal) {
                                    'up' => '↗ Up',
                                    'down' => '↘ Down',
                                    default => '→ Same',
                                };
                            @endphp
                            <span class="inline-flex items-center text-xs {{ $trendClass }}">
                                {{ $trendSymbol }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-10 text-center text-gray-500 dark:text-gray-400" colspan="6">
                            <div class="flex flex-col items-center justify-center space-y-1">
                                <span class="text-sm font-medium">No performance summaries computed yet</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Quarterly evaluations will be summarized here automatically as teacher scores are posted.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
