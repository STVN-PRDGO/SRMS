@php
    use App\Filament\Teacher\Pages\MyStudents;
    use App\Filament\Teacher\Resources\ActivityResource;

    $gradedValues = $grades->pluck('raw_grade')->filter(fn ($grade) => $grade !== null);
    $overallAverage = $gradedValues->isNotEmpty() ? $gradedValues->avg() : null;
    $averageForGauge = min(100, max(0, (float) ($overallAverage ?? 0)));
    $subjectCount = $grades->map(fn ($grade) => $grade->classSubject->subject_id)->unique()->count();
    $gradedActivityCount = $activities->filter(fn ($activity) => $activity->scores->first()?->status?->value === 'graded')->count();
@endphp

<div class="srms-record">
    <div class="srms-dashboard-top">
        <section class="srms-profile-hero" aria-labelledby="profile-heading">
            <div class="srms-profile-hero-main">
                <div class="srms-avatar" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none"><circle cx="24" cy="17" r="9"/><path d="M7 43c1.8-9.2 8.3-14 17-14s15.2 4.8 17 14"/></svg>
                </div>
                <div class="srms-profile-identity">
                    <p class="srms-eyebrow">Student profile</p>
                    <h1 id="profile-heading">{{ $student->user->name }}</h1>
                    <p>{{ $student->section->gradeLevel->name }} · {{ $student->section->name }} <span>·</span> {{ $student->student_no }}</p>
                </div>
                <span @class(['srms-status', 'is-active' => $student->status->value === 'active'])>{{ $student->status->getLabel() }}</span>
            </div>
            <dl class="srms-profile-details">
                <div><dt>School year</dt><dd>{{ $student->section->schoolYear->name }}</dd></div>
                <div><dt>Birthdate</dt><dd>{{ $student->birthdate?->format('M j, Y') ?? 'Not recorded' }}</dd></div>
                <div><dt>Guardian</dt><dd>{{ $student->guardian_name ?: 'Not recorded' }}</dd></div>
                <div><dt>Guardian contact</dt><dd>{{ $student->guardian_contact ?: 'Not recorded' }}</dd></div>
            </dl>
            <a class="srms-back" href="{{ MyStudents::getUrl() }}" wire:navigate><span aria-hidden="true">←</span> Back to my students</a>
        </section>

        <section class="srms-performance" aria-labelledby="performance-heading">
            <div class="srms-performance-overview">
                <div class="srms-section-heading">
                    <p class="srms-eyebrow">Academic performance</p>
                    <h2 id="performance-heading">Performance overview</h2>
                </div>
                <div class="srms-performance-main">
                    <div class="srms-grade-ring" style="--grade-progress: {{ $averageForGauge }}%" role="img" aria-label="Overall average {{ $overallAverage !== null ? number_format((float) $overallAverage, 2) : 'not available' }}{{ $overallAverage !== null ? ' percent' : '' }}">
                        <div><strong>{{ $overallAverage !== null ? number_format((float) $overallAverage, 2).'%' : '—' }}</strong><span>Overall average</span></div>
                    </div>
                    <div class="srms-performance-divider" aria-hidden="true"></div>
                    <dl class="srms-quick-stats">
                        <div><dt><span class="srms-stat-icon">▦</span> Subjects</dt><dd>{{ $subjectCount }}</dd></div>
                        <div><dt><span class="srms-stat-icon is-green">✓</span> Computed grades</dt><dd>{{ $grades->count() }}</dd></div>
                        <div><dt><span class="srms-stat-icon is-amber">◷</span> Graded activities</dt><dd>{{ $gradedActivityCount }}<span> / {{ $activities->count() }}</span></dd></div>
                    </dl>
                </div>
            </div>
        </section>
    </div>

    <section class="srms-grade-panel" aria-labelledby="grade-heading">
        <div class="srms-section-heading srms-grade-heading">
            <div class="srms-heading-title"><span class="srms-heading-icon" aria-hidden="true">★</span><h2 id="grade-heading">Computed grades</h2></div>
            <span class="srms-grade-count">{{ $grades->count() }} {{ $grades->count() === 1 ? 'grade' : 'grades' }}</span>
        </div>
        <form class="srms-term-filter" wire:submit="applyTerm" aria-labelledby="term-legend">
            <fieldset>
                <legend id="term-legend">Filter scores by term</legend>
                @if ($terms->count() <= 4)
                    <div class="srms-terms">
                        <div><input id="term-all" type="radio" name="term" value="" wire:model="term"><label for="term-all">All terms</label></div>
                        @foreach ($terms as $period)
                            <div><input id="term-{{ $period->id }}" type="radio" name="term" value="{{ $period->id }}" wire:model="term"><label for="term-{{ $period->id }}">{{ $period->name }}</label></div>
                        @endforeach
                    </div>
                @else
                    <div class="srms-field">
                        <label for="term-select">Term</label>
                        <select id="term-select" name="term" wire:model="term">
                            <option value="">All terms</option>
                            @foreach ($terms as $period)
                                <option value="{{ $period->id }}">{{ $period->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit">Apply term</button>
            </fieldset>
        </form>
        @if ($grades->isEmpty())
            <p class="srms-muted">No computed grade for this term yet. It appears after a score is saved.</p>
        @else
            <dl class="srms-grades">
                @foreach ($grades as $grade)
                    <div class="srms-grade-card">
                        <dt>{{ $grade->classSubject->subject->name }}<span>{{ $grade->gradingPeriod->name }}</span></dt>
                        <dd><strong>{{ $grade->raw_grade !== null ? number_format((float) $grade->raw_grade, 2) : '—' }}</strong><span>{{ $grade->letter ?: 'No mark' }} · {{ $grade->remark ?: 'No remark' }}</span></dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>

    @if ($errorMessage !== '')
        <p class="srms-alert" role="alert">{{ $errorMessage }}</p>
    @endif

    @if ($activities->isEmpty())
        <div class="srms-empty">
            <h2>No encoded scores for this term</h2>
            <p>Create a quiz, activity, project, midterm, or final, then the score can be entered here.</p>
            <a href="{{ ActivityResource::getUrl() }}">Go to activities</a>
        </div>
    @else
        <form class="srms-score-form" wire:submit="save" aria-labelledby="score-caption">
            <div class="srms-section-heading srms-score-heading">
                <div class="srms-heading-title"><span class="srms-heading-icon" aria-hidden="true">☷</span><h2>Encoded scores</h2></div>
                <span class="srms-grade-count">{{ $activities->count() }} activities</span>
            </div>
            <div class="srms-table-wrap" tabindex="0" role="region" aria-labelledby="score-caption">
                <table class="srms-score-table">
                    <caption id="score-caption">Encoded scores</caption>
                    <thead>
                        <tr>
                            <th scope="col">Activity</th>
                            <th scope="col">Type</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Term</th>
                            <th scope="col">Max</th>
                            <th scope="col">Score</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            @php($open = $activity->gradingPeriod->acceptsEntries())
                            <tr>
                                <th scope="row">{{ $activity->title }}</th>
                                <td>{{ $activity->type->getLabel() }}</td>
                                <td>{{ $activity->classSubject->subject->name }}</td>
                                <td>{{ $activity->gradingPeriod->name }}{{ $open ? '' : ' (read only)' }}</td>
                                <td class="srms-num">{{ number_format((float) $activity->max_score, 2) }}</td>
                                <td>
                                    <label class="sr-only" for="score-{{ $activity->id }}">Score for {{ $activity->title }}</label>
                                    <input id="score-{{ $activity->id }}" type="text" inputmode="decimal" wire:model="rows.{{ $activity->id }}.score" @disabled(! $open)>
                                </td>
                                <td>
                                    <label class="sr-only" for="status-{{ $activity->id }}">Status for {{ $activity->title }}</label>
                                    <select id="status-{{ $activity->id }}" wire:model="rows.{{ $activity->id }}.status" @disabled(! $open)>
                                        <option value="">Not recorded</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit">Save changes</button>
        </form>
    @endif
</div>
