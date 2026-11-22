<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="border-b border-gray-200 px-5 py-4 text-base font-semibold text-gray-900 dark:border-gray-800 dark:text-gray-100 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
        <span>Class Student Grades</span>
        <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Latest computed records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-gray-700 dark:text-gray-200">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/75 dark:bg-gray-800/75">
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Student</th>
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Subject</th>
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Period</th>
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Raw Grade</th>
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Scale Letter</th>
                    <th class="px-5 py-3 text-left font-semibold text-xs uppercase tracking-wider text-gray-600 dark:text-gray-400">Remark</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $grade)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition">
                        <td class="px-5 py-3.5 font-medium text-gray-900 dark:text-white">
                            {{ $grade->student->user->name }}
                            <span class="text-xs text-gray-400 dark:text-gray-500 block">({{ $grade->student->student_no }})</span>
                        </td>
                        <td class="px-5 py-3.5">{{ $grade->classSubject->subject->name }}</td>
                        <td class="px-5 py-3.5">{{ $grade->gradingPeriod->name }}</td>
                        <td class="px-5 py-3.5 font-semibold text-gray-900 dark:text-white">
                            {{ $grade->raw_grade !== null ? number_format($grade->raw_grade, 2) . '%' : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $letter = $grade->letter ?? '—';
                                $badgeClass = match ($letter) {
                                    'A' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
                                    'B' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
                                    'C' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
                                    'D' => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/20',
                                    'F' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
                                    default => 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $badgeClass }}">
                                {{ $letter }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500 dark:text-gray-400">{{ $grade->remark ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-10 text-center text-gray-500 dark:text-gray-400" colspan="6">
                            <div class="flex flex-col items-center justify-center space-y-1">
                                <span class="text-sm font-medium">No computed grades recorded yet</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Grades calculate automatically after student scores are recorded for class activities.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
