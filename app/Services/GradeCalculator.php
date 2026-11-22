<?php

namespace App\Services;

use App\Enums\ScoreStatus;
use App\Models\Activity;
use App\Models\ClassSubject;
use App\Models\GradeScale;
use App\Models\GradingComponent;
use App\Models\GradingPeriod;
use App\Models\Student;
use Illuminate\Support\Collection;

class GradeCalculator
{
    /**
     * Percentage grade for one student in one class during one period.
     *
     * Missing scores count as zero. Excused scores are left out. A score row
     * that does not exist yet is left out, so unfinished entry does not
     * become a zero. Components with nothing countable drop out and the
     * remaining weights are scaled to 100. A max score of zero is skipped.
     *
     * @return array{raw_grade: ?float, letter: ?string, remark: ?string}
     */
    public function calculate(Student $student, ClassSubject $classSubject, GradingPeriod $period): array
    {
        $activities = Activity::query()
            ->where('class_subject_id', $classSubject->id)
            ->where('grading_period_id', $period->id)
            ->with(['scores' => fn ($query) => $query->where('student_id', $student->id)])
            ->get();

        $components = $this->componentsFor($classSubject->subject_id);
        $weighted = 0.0;
        $weightUsed = 0.0;

        foreach ($components as $component) {
            $componentActivities = $activities->where('grading_component_id', $component->id);
            $percent = $this->componentPercent($componentActivities);

            if ($percent === null) {
                continue;
            }

            $weighted += $percent * (float) $component->weight;
            $weightUsed += (float) $component->weight;
        }

        if ($weightUsed <= 0) {
            return ['raw_grade' => null, 'letter' => null, 'remark' => null];
        }

        $raw = round($weighted / $weightUsed, 2, PHP_ROUND_HALF_UP);
        $scale = $this->scaleFor($raw);

        return [
            'raw_grade' => $raw,
            'letter' => $scale?->letter,
            'remark' => $scale?->remark,
        ];
    }

    /**
     * @param  Collection<int, Activity>  $activities
     */
    private function componentPercent(Collection $activities): ?float
    {
        $total = 0.0;
        $count = 0;

        foreach ($activities as $activity) {
            $max = (float) $activity->max_score;

            if ($max <= 0) {
                continue;
            }

            $score = $activity->scores->first();

            if ($score === null || $score->status === ScoreStatus::Excused) {
                continue;
            }

            $earned = $score->status === ScoreStatus::Missing ? 0.0 : (float) $score->score;
            $total += ($earned / $max) * 100;
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return $total / $count;
    }

    /**
     * @return Collection<int, GradingComponent>
     */
    private function componentsFor(int $subjectId): Collection
    {
        /** @var Collection<int, GradingComponent> $all */
        $all = cache()->rememberForever('grading_components.all', fn () => GradingComponent::all());
        $own = $all->where('subject_id', $subjectId);

        if ($own->isNotEmpty()) {
            return $own;
        }

        return $all->whereNull('subject_id');
    }

    private function scaleFor(float $grade): ?GradeScale
    {
        /** @var Collection<int, GradeScale> $scales */
        $scales = cache()->rememberForever('grade_scales.all', fn () => GradeScale::query()->orderByDesc('min_grade')->get());

        return $scales->first(
            fn (GradeScale $scale) => $grade >= (float) $scale->min_grade && $grade <= (float) $scale->max_grade
        );
    }
}
