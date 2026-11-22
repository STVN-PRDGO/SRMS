@php
    use App\Filament\Teacher\Pages\StudentRecord;
@endphp

<div class="srms-directory">
    <section class="srms-directory-hero" aria-labelledby="students-heading">
        <div class="srms-directory-hero-copy">
            <p class="srms-eyebrow">Your classroom, at a glance</p>
            <h1 id="students-heading">My Students</h1>
            <p>Manage the students enrolled in your classes.</p>
        </div>
        <div class="srms-directory-decoration" aria-hidden="true"></div>
    </section>

    <search class="srms-directory-search">
        <form wire:submit="applySearch" aria-labelledby="student-search-legend">
            <fieldset>
                <legend id="student-search-legend">Find a student</legend>
                <div class="srms-field">
                    <label for="student-query">Name or student number</label>
                    <input id="student-query" name="query" type="search" wire:model="query" placeholder="Search by name or student number…" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" enterkeyhint="search">
                </div>
                <button type="submit">Search</button>
            </fieldset>
        </form>
    </search>

    @if ($students->isEmpty())
        <div class="srms-empty">
            <h2>{{ $appliedQuery === '' ? 'No students in your classes yet' : 'No students match that search' }}</h2>
            <p>{{ $appliedQuery === '' ? 'Students appear here after they are enrolled in a section you teach.' : 'Try another name or student number.' }}</p>
        </div>
    @else
        <ul class="srms-student-grid">
            @foreach ($students as $student)
                @php
                    $subjects = $student->section->classSubjects
                        ->filter(fn ($classSubject) => $classSubject->isManageableBy(auth()->user()))
                        ->map(fn ($classSubject) => $classSubject->subject->name)
                        ->unique()
                        ->values();
                    $initials = collect(preg_split('/\s+/', trim($student->user->name)) ?: [])
                        ->filter()
                        ->take(2)
                        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->join('');
                @endphp
                <li>
                    <a class="srms-student-link" href="{{ StudentRecord::getUrl(['student' => $student]) }}" wire:navigate aria-label="View {{ $student->user->name }}’s student record">
                        <article class="srms-student-card">
                            <header>
                                <p class="srms-initials" aria-hidden="true">{{ $initials }}</p>
                                <div class="srms-student-identity">
                                    <span class="srms-status is-{{ $student->status->value }}"><span aria-hidden="true"></span>{{ $student->status->getLabel() }}</span>
                                    <h2>{{ $student->user->name }}</h2>
                                </div>
                                <span class="srms-card-arrow" aria-hidden="true">↗</span>
                            </header>
                            <dl>
                                <div>
                                    <dt>Student number</dt>
                                    <dd>{{ $student->student_no }}</dd>
                                </div>
                                <div>
                                    <dt>Section</dt>
                                    <dd>{{ $student->section->gradeLevel->name }} · {{ $student->section->name }}</dd>
                                </div>
                                <div>
                                    <dt>School year</dt>
                                    <dd>{{ $student->section->schoolYear->name }}</dd>
                                </div>
                            </dl>
                            <div class="srms-student-subjects">
                                <span>Subjects</span>
                                <p>{{ $subjects->take(2)->join(', ') ?: 'No assigned subjects' }}@if ($subjects->count() > 2)<span class="srms-subject-more">+{{ $subjects->count() - 2 }} more</span>@endif</p>
                            </div>
                            <span class="srms-card-action">View details <span aria-hidden="true">→</span></span>
                        </article>
                    </a>
                </li>
            @endforeach
        </ul>

        <footer class="srms-directory-footer">
            <p>Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students</p>
            {{ $students->links() }}
        </footer>
    @endif
</div>
