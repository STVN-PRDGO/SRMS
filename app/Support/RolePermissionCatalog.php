<?php

namespace App\Support;

class RolePermissionCatalog
{
    /** @var list<string> */
    public const METHODS = [
        'viewAny', 'view', 'create', 'update', 'delete', 'deleteAny',
        'restore', 'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder',
    ];

    /** @var list<string> */
    public const READ = ['viewAny', 'view'];

    /** @var list<string> */
    public const CUSTOM = [
        'access_admin_panel',
        'access_teacher_panel',
        'access_student_panel',
        'view_all_classes',
        'view_as_student',
        'close_grading_period',
        'reopen_grading_period',
        'export_class_record',
        'download_report_card',
    ];

    /**
     * @return array<string, array<string, list<string>>>
     */
    public static function resources(): array
    {
        return [
            'admin' => [
                'user' => self::METHODS,
                'teacher' => self::METHODS,
                'student' => self::METHODS,
                'school_year' => self::METHODS,
                'grading_period' => self::METHODS,
                'grade_level' => self::METHODS,
                'section' => self::METHODS,
                'subject' => self::METHODS,
                'class_subject' => self::METHODS,
                'grading_component' => self::METHODS,
                'grade_scale' => self::METHODS,
                'performance_level' => self::METHODS,
                'role' => ['viewAny', 'view', 'create', 'update', 'delete'],
                'audit' => self::READ,
            ],
            'teacher' => [
                'class_subject' => self::METHODS,
                'activity' => self::METHODS,
                'score' => self::METHODS,
                'attendance' => self::METHODS,
            ],
            'student' => [
                'grade' => self::READ,
                'activity' => self::READ,
                'attendance' => self::READ,
                'performance_summary' => self::READ,
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function pages(): array
    {
        return [
            'admin' => ['section_averages', 'honor_list', 'at_risk_list', 'profile'],
            'teacher' => ['score_entry', 'class_performance', 'profile'],
            'student' => ['performance_overview', 'profile', 'select_student'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function widgets(): array
    {
        return [
            'admin' => [
                'admin_overview_stats_widget',
                'grade_distribution_chart_widget',
                'section_performance_chart_widget',
                'attendance_health_chart_widget',
            ],
            'teacher' => [
                'class_average_widget',
                'pending_grading_widget',
                'at_risk_widget',
                'teacher_overview_stats_widget',
                'teacher_class_performance_chart_widget',
                'teacher_grade_distribution_chart_widget',
            ],
            'student' => [
                'performance_trend_widget',
                'student_overview_stats_widget',
                'student_subject_breakdown_chart_widget',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $resources = [];

        foreach (self::resources() as $panel => $subjects) {
            foreach (array_keys($subjects) as $subject) {
                $resources[$panel][$subject] = $subject === 'role'
                    ? ['viewAny', 'view', 'create', 'update', 'delete']
                    : self::METHODS;
            }
        }

        return array_values(array_unique([
            ...self::CUSTOM,
            ...self::namesFor($resources, self::pages(), self::widgets()),
        ]));
    }

    /**
     * @return list<string>
     */
    public static function forRole(string $role): array
    {
        $resources = self::resources();
        $pages = self::pages();
        $widgets = self::widgets();

        $names = match ($role) {
            'admin' => [
                ...self::CUSTOM,
                ...self::namesFor($resources, $pages, $widgets),
            ],
            'teacher' => [
                'access_teacher_panel',
                'close_grading_period',
                'export_class_record',
                ...self::flatten($resources['teacher'], 'teacher'),
                ...self::flatten(array_fill_keys($pages['teacher'], ['view']), 'teacher'),
                ...self::flatten(array_fill_keys($widgets['teacher'], ['view']), 'teacher'),
            ],
            'student' => [
                'access_student_panel',
                'download_report_card',
                ...self::flatten($resources['student'], 'student'),
                ...self::flatten(array_fill_keys(
                    array_values(array_filter($pages['student'], fn (string $page): bool => $page !== 'select_student')),
                    ['view'],
                ), 'student'),
                ...self::flatten(array_fill_keys($widgets['student'], ['view']), 'student'),
            ],
            default => [],
        };

        return array_values(array_unique($names));
    }

    /**
     * @param  array<string, array<string, list<string>>>  $resources
     * @param  array<string, list<string>>  $pages
     * @param  array<string, list<string>>  $widgets
     * @return list<string>
     */
    private static function namesFor(array $resources, array $pages, array $widgets): array
    {
        $names = [];

        foreach ($resources as $panel => $subjects) {
            $names = [...$names, ...self::flatten($subjects, $panel)];
        }

        foreach ($pages as $panel => $subjects) {
            $names = [...$names, ...self::flatten(array_fill_keys($subjects, ['view']), $panel)];
        }

        foreach ($widgets as $panel => $subjects) {
            $names = [...$names, ...self::flatten(array_fill_keys($subjects, ['view']), $panel)];
        }

        return $names;
    }

    /**
     * @param  array<string, list<string>>  $subjects
     * @return list<string>
     */
    private static function flatten(array $subjects, string $panel): array
    {
        $names = [];

        foreach ($subjects as $subject => $methods) {
            foreach ($methods as $method) {
                $names[] = PermissionName::for($method, $subject, $panel);
            }
        }

        return $names;
    }
}
