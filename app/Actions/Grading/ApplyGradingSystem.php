<?php

namespace App\Actions\Grading;

use App\Enums\GradingTrack;
use App\Models\Activity;
use App\Models\GradeScale;
use App\Models\GradingComponent;
use App\Models\PerformanceLevel;
use App\Models\SchoolSetting;
use App\Support\PhilippineGrading;
use Illuminate\Support\Facades\DB;

class ApplyGradingSystem
{
    public function __invoke(GradingTrack $track): void
    {
        DB::transaction(function () use ($track): void {
            $setting = SchoolSetting::query()->first() ?? new SchoolSetting;
            $setting->grading_track = $track;
            $setting->save();

            $this->components($track);
            $this->scales($track);
            $this->levels($track);
        });
    }

    private function components(GradingTrack $track): void
    {
        $presets = PhilippineGrading::components($track);

        foreach (['Quiz' => 'Quizzes', 'Activity' => 'Activities', 'Project' => 'Projects', 'Exam' => 'Final'] as $from => $to) {
            $old = GradingComponent::query()->whereNull('subject_id')->where('name', $from)->first();
            $target = GradingComponent::query()->whereNull('subject_id')->where('name', $to)->first();

            if ($old === null) {
                continue;
            }

            if ($target === null) {
                $old->name = $to;
                $old->save();

                continue;
            }

            Activity::query()->where('grading_component_id', $old->id)->update(['grading_component_id' => $target->id]);
            $old->delete();
        }

        foreach ($presets as $name => $weight) {
            GradingComponent::withoutEvents(function () use ($name, $weight): void {
                GradingComponent::query()->updateOrCreate(
                    ['subject_id' => null, 'name' => $name],
                    ['weight' => $weight],
                );
            });
        }

        GradingComponent::query()
            ->whereNull('subject_id')
            ->whereNotIn('name', array_keys($presets))
            ->whereDoesntHave('activities')
            ->delete();

        cache()->forget('grading_components.all');
    }

    private function scales(GradingTrack $track): void
    {
        GradeScale::query()->delete();

        foreach (PhilippineGrading::gradeScales($track) as $scale) {
            GradeScale::query()->create($scale);
        }
    }

    private function levels(GradingTrack $track): void
    {
        PerformanceLevel::query()->delete();

        foreach (PhilippineGrading::performanceLevels($track) as $level) {
            PerformanceLevel::query()->create($level);
        }
    }
}
