<?php

namespace Database\Seeders;

use App\Models\GradeScale;
use App\Models\PerformanceLevel;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        if (GradeScale::query()->exists()) {
            return;
        }

        $scales = [
            ['min_grade' => 90, 'max_grade' => 100, 'letter' => 'A', 'remark' => 'Outstanding'],
            ['min_grade' => 85, 'max_grade' => 89.99, 'letter' => 'B', 'remark' => 'Very Satisfactory'],
            ['min_grade' => 80, 'max_grade' => 84.99, 'letter' => 'C', 'remark' => 'Satisfactory'],
            ['min_grade' => 75, 'max_grade' => 79.99, 'letter' => 'D', 'remark' => 'Fairly Satisfactory'],
            ['min_grade' => 0, 'max_grade' => 74.99, 'letter' => 'F', 'remark' => 'Did Not Meet Expectations'],
        ];

        foreach ($scales as $scale) {
            GradeScale::query()->create($scale);
        }

        $levels = [
            ['name' => 'Outstanding', 'min_grade' => 90, 'max_grade' => 100, 'min_attendance' => 90, 'order' => 1],
            ['name' => 'Very Satisfactory', 'min_grade' => 85, 'max_grade' => 89.99, 'min_attendance' => 85, 'order' => 2],
            ['name' => 'Satisfactory', 'min_grade' => 80, 'max_grade' => 84.99, 'min_attendance' => 80, 'order' => 3],
            ['name' => 'Fairly Satisfactory', 'min_grade' => 75, 'max_grade' => 79.99, 'min_attendance' => 75, 'order' => 4],
            ['name' => 'Needs Improvement', 'min_grade' => 0, 'max_grade' => 74.99, 'min_attendance' => null, 'order' => 5],
        ];

        foreach ($levels as $level) {
            PerformanceLevel::query()->create($level);
        }
    }
}
