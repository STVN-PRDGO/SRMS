<?php

namespace App\Actions\Scores;

use App\Enums\ScoreStatus;
use App\Jobs\RecalculateStudentGradeJob;
use App\Models\Activity;
use App\Models\Score;
use App\Models\User;
use App\Support\PermissionName;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveScores
{
    /**
     * @param  list<array{student_id: int, score?: mixed, status?: string, remarks?: ?string}>  $entries
     */
    public function __invoke(Activity $activity, array $entries, User $grader): void
    {
        $activity->loadMissing('gradingPeriod', 'classSubject.section');

        if (! $grader->can(PermissionName::for('update', 'score', 'teacher'))) {
            throw new AuthorizationException('You cannot update scores.');
        }

        if (! $activity->classSubject->isManageableBy($grader)) {
            throw new AuthorizationException('You cannot update scores for this class.');
        }

        if (! $activity->gradingPeriod->acceptsEntries()) {
            throw ValidationException::withMessages([
                'activity' => 'Scores can only be saved while the grading period is open.',
            ]);
        }

        $max = (float) $activity->max_score;

        if ($max <= 0) {
            throw ValidationException::withMessages([
                'activity' => 'This activity has no usable maximum score.',
            ]);
        }

        $studentIds = $activity->classSubject->section->students()->pluck('id');
        $now = now();
        $upsertRows = [];
        $affectedStudentIds = [];

        foreach ($entries as $entry) {
            $studentId = (int) ($entry['student_id'] ?? 0);

            if (! $studentIds->contains($studentId)) {
                throw ValidationException::withMessages([
                    'scores' => 'One of the students is not on this class roster.',
                ]);
            }

            $status = ScoreStatus::tryFrom((string) ($entry['status'] ?? ''));

            if ($status === null && isset($entry['score']) && $entry['score'] !== '') {
                $status = ScoreStatus::Graded;
            }

            if ($status === null) {
                continue;
            }

            $score = $entry['score'] ?? null;
            $score = $score === '' || $score === null ? null : (float) $score;

            if ($status === ScoreStatus::Graded && ($score === null || $score < 0 || $score > $max)) {
                throw ValidationException::withMessages([
                    'scores' => "Each graded score must be between 0 and the activity maximum ({$max}).",
                ]);
            }

            $upsertRows[] = [
                'activity_id' => $activity->id,
                'student_id' => $studentId,
                'score' => $status === ScoreStatus::Graded ? $score : null,
                'status' => $status->value,
                'remarks' => $entry['remarks'] ?? null,
                'graded_by' => $grader->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $affectedStudentIds[] = $studentId;
        }

        if (! empty($upsertRows)) {
            DB::transaction(function () use ($upsertRows): void {
                Score::query()->upsert(
                    $upsertRows,
                    ['activity_id', 'student_id'],
                    ['score', 'status', 'remarks', 'graded_by', 'updated_at']
                );
            });

            $classSubjectId = $activity->class_subject_id;
            $gradingPeriodId = $activity->grading_period_id;

            foreach (array_unique($affectedStudentIds) as $studentId) {
                RecalculateStudentGradeJob::dispatch(
                    $studentId,
                    $classSubjectId,
                    $gradingPeriodId,
                )->afterCommit();
            }

            Cache::forget("dash.teacher.{$grader->id}");
        }
    }
}
