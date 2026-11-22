<?php

namespace App\Support;

use App\Enums\GradingTrack;

class PhilippineGrading
{
    /**
     * @return array<string, float>
     */
    public static function components(GradingTrack $track): array
    {
        return match ($track) {
            GradingTrack::HighSchool => [
                'Quizzes' => 20,
                'Activities' => 20,
                'Projects' => 40,
                'Midterm' => 10,
                'Final' => 10,
            ],
            GradingTrack::College => [
                'Quizzes' => 20,
                'Activities' => 15,
                'Projects' => 15,
                'Midterm' => 25,
                'Final' => 25,
            ],
        };
    }

    /**
     * DepEd Order No. 8, s. 2015 transmutation table.
     * Initial weighted percentage to quarterly grade.
     *
     * @var list<array{0: float, 1: float, 2: int}>
     */
    private const TRANSMUTATION = [
        [100.00, 100.00, 100],
        [98.40, 99.99, 99],
        [96.80, 98.39, 98],
        [95.20, 96.79, 97],
        [93.60, 95.19, 96],
        [92.00, 93.59, 95],
        [90.40, 91.99, 94],
        [88.80, 90.39, 93],
        [87.20, 88.79, 92],
        [85.60, 87.19, 91],
        [84.00, 85.59, 90],
        [82.40, 83.99, 89],
        [80.80, 82.39, 88],
        [79.20, 80.79, 87],
        [77.60, 79.19, 86],
        [76.00, 77.59, 85],
        [74.40, 75.99, 84],
        [72.80, 74.39, 83],
        [71.20, 72.79, 82],
        [69.60, 71.19, 81],
        [68.00, 69.59, 80],
        [66.40, 67.99, 79],
        [64.80, 66.39, 78],
        [63.20, 64.79, 77],
        [61.60, 63.19, 76],
        [60.00, 61.59, 75],
        [56.00, 59.99, 74],
        [52.00, 55.99, 73],
        [48.00, 51.99, 72],
        [44.00, 47.99, 71],
        [40.00, 43.99, 70],
        [36.00, 39.99, 69],
        [32.00, 35.99, 68],
        [28.00, 31.99, 67],
        [24.00, 27.99, 66],
        [20.00, 23.99, 65],
        [16.00, 19.99, 64],
        [12.00, 15.99, 63],
        [8.00, 11.99, 62],
        [4.00, 7.99, 61],
        [0.00, 3.99, 60],
    ];

    public static function quarterlyGrade(float $initial): float
    {
        $initial = round(max(0, min(100, $initial)), 2);

        foreach (self::TRANSMUTATION as [$min, $max, $quarterly]) {
            if ($initial >= $min && $initial <= $max) {
                return (float) $quarterly;
            }
        }

        return 60.0;
    }

    /**
     * @return list<array{min_grade: float, max_grade: float, letter: string, remark: string}>
     */
    public static function gradeScales(GradingTrack $track): array
    {
        return match ($track) {
            GradingTrack::HighSchool => [
                ['min_grade' => 90, 'max_grade' => 100, 'letter' => 'A', 'remark' => 'Outstanding'],
                ['min_grade' => 85, 'max_grade' => 89.99, 'letter' => 'B', 'remark' => 'Very Satisfactory'],
                ['min_grade' => 80, 'max_grade' => 84.99, 'letter' => 'C', 'remark' => 'Satisfactory'],
                ['min_grade' => 75, 'max_grade' => 79.99, 'letter' => 'D', 'remark' => 'Fairly Satisfactory'],
                ['min_grade' => 0, 'max_grade' => 74.99, 'letter' => 'F', 'remark' => 'Did Not Meet Expectations'],
            ],
            GradingTrack::College => [
                ['min_grade' => 97, 'max_grade' => 100, 'letter' => '1.00', 'remark' => 'Excellent'],
                ['min_grade' => 94, 'max_grade' => 96.99, 'letter' => '1.25', 'remark' => 'Superior'],
                ['min_grade' => 91, 'max_grade' => 93.99, 'letter' => '1.50', 'remark' => 'Very Good'],
                ['min_grade' => 88, 'max_grade' => 90.99, 'letter' => '1.75', 'remark' => 'Good'],
                ['min_grade' => 85, 'max_grade' => 87.99, 'letter' => '2.00', 'remark' => 'Satisfactory'],
                ['min_grade' => 82, 'max_grade' => 84.99, 'letter' => '2.25', 'remark' => 'Fairly Satisfactory'],
                ['min_grade' => 79, 'max_grade' => 81.99, 'letter' => '2.50', 'remark' => 'Fair'],
                ['min_grade' => 76, 'max_grade' => 78.99, 'letter' => '2.75', 'remark' => 'Passed'],
                ['min_grade' => 75, 'max_grade' => 75.99, 'letter' => '3.00', 'remark' => 'Passed'],
                ['min_grade' => 0, 'max_grade' => 74.99, 'letter' => '5.00', 'remark' => 'Failed'],
            ],
        };
    }

    /**
     * @return list<array{name: string, min_grade: float, max_grade: float, min_attendance: ?float, order: int}>
     */
    public static function performanceLevels(GradingTrack $track): array
    {
        return match ($track) {
            GradingTrack::HighSchool => [
                ['name' => 'Outstanding', 'min_grade' => 90, 'max_grade' => 100, 'min_attendance' => 90, 'order' => 1],
                ['name' => 'Very Satisfactory', 'min_grade' => 85, 'max_grade' => 89.99, 'min_attendance' => 85, 'order' => 2],
                ['name' => 'Satisfactory', 'min_grade' => 80, 'max_grade' => 84.99, 'min_attendance' => 80, 'order' => 3],
                ['name' => 'Fairly Satisfactory', 'min_grade' => 75, 'max_grade' => 79.99, 'min_attendance' => 75, 'order' => 4],
                ['name' => 'Did Not Meet Expectations', 'min_grade' => 0, 'max_grade' => 74.99, 'min_attendance' => null, 'order' => 5],
            ],
            GradingTrack::College => [
                ['name' => 'Excellent', 'min_grade' => 97, 'max_grade' => 100, 'min_attendance' => null, 'order' => 1],
                ['name' => 'Superior', 'min_grade' => 94, 'max_grade' => 96.99, 'min_attendance' => null, 'order' => 2],
                ['name' => 'Very Good', 'min_grade' => 91, 'max_grade' => 93.99, 'min_attendance' => null, 'order' => 3],
                ['name' => 'Good', 'min_grade' => 88, 'max_grade' => 90.99, 'min_attendance' => null, 'order' => 4],
                ['name' => 'Satisfactory', 'min_grade' => 85, 'max_grade' => 87.99, 'min_attendance' => null, 'order' => 5],
                ['name' => 'Passed', 'min_grade' => 75, 'max_grade' => 84.99, 'min_attendance' => null, 'order' => 6],
                ['name' => 'Failed', 'min_grade' => 0, 'max_grade' => 74.99, 'min_attendance' => null, 'order' => 7],
            ],
        };
    }

    /**
     * @return array{letter: ?string, remark: ?string}
     */
    public static function mark(GradingTrack $track, float $grade): array
    {
        foreach (self::gradeScales($track) as $scale) {
            if ($grade >= $scale['min_grade'] && $grade <= $scale['max_grade']) {
                return ['letter' => $scale['letter'], 'remark' => $scale['remark']];
            }
        }

        return ['letter' => null, 'remark' => null];
    }
}
