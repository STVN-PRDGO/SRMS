# 04 - Project Structure

## 1. Folder Layout

```
student-records/
├── app/
│   ├── Actions/
│   │   ├── Grades/RecalculateStudentGrade.php
│   │   └── Periods/{ClosePeriod,ReopenPeriod}.php
│   ├── Enums/
│   ├── Filament/
│   │   ├── Admin/
│   │   │   ├── Resources/        # User, Teacher, Student, SchoolYear, GradingPeriod,
│   │   │   │                     # GradeLevel, Section, Subject, ClassSubject,
│   │   │   │                     # GradingComponent, GradeScale, PerformanceLevel
│   │   │   ├── Pages/            # Dashboard, AtRiskList, HonorList, SectionAverages, Profile
│   │   │   └── Widgets/          # AdminOverviewStatsWidget, GradeDistributionChartWidget,
│   │   │                         # SectionPerformanceChartWidget, AttendanceHealthChartWidget
│   │   ├── Teacher/
│   │   │   ├── Resources/        # MyClassResource, ActivityResource, ScoreResource, AttendanceResource
│   │   │   ├── Pages/            # Dashboard, ScoreEntry, ClassPerformance, Profile
│   │   │   └── Widgets/          # TeacherOverviewStatsWidget, TeacherClassPerformanceChartWidget,
│   │   │                         # TeacherGradeDistributionChartWidget, AtRiskWidget, PendingGradingWidget
│   │   ├── Student/
│   │   │   ├── Resources/        # GradeResource, ActivityResource, AttendanceResource
│   │   │   ├── Pages/            # Dashboard, PerformanceOverview, Profile, SelectStudent
│   │   │   └── Widgets/          # StudentOverviewStatsWidget, StudentSubjectBreakdownChartWidget,
│   │   │                         # PerformanceTrendWidget
│   │   └── Shared/               # reusable form fields, table columns, traits, welcome banner
│   ├── Jobs/RecalculateStudentGradeJob.php
│   ├── Models/
│   ├── Observers/{ScoreObserver,AttendanceObserver,ActivityObserver}.php
│   ├── Policies/
│   ├── Providers/Filament/
│   │   ├── AdminPanelProvider.php
│   │   ├── TeacherPanelProvider.php
│   │   └── StudentPanelProvider.php
│   └── Services/
│       ├── AnalyticsService.php
│       ├── GradeCalculator.php
│       ├── PerformanceEvaluator.php
│       └── ReportCardService.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── *.md                           # planning documents (currently kept at project root)
├── resources/
│   ├── css/filament/{admin,teacher,student}/theme.css
│   └── views/{reports,filament}/
├── routes/
├── tests/
│   ├── Feature/{Panels,Policies,Scores}/
│   └── Unit/{GradeCalculatorTest,PerformanceEvaluatorTest}.php
├── tailwind.config.js
├── vite.config.js
└── README.md
```

## 2. Naming Conventions

| Item | Convention | Example |
|------|-----------|---------|
| Models | Singular PascalCase | `ClassSubject` |
| Tables | Plural snake_case | `class_subjects` |
| Resources | `{Model}Resource` | `ScoreResource` |
| Services | `{Thing}Service` / noun | `GradeCalculator` |
| Actions | Verb phrase, single `__invoke` or `handle` | `ClosePeriod` |
| Enums | PascalCase, backed string | `AttendanceStatus::Present` |
| Panel resources | Namespaced per panel | `App\Filament\Teacher\Resources\...` |

## 3. Code Rules

1. No business logic in Filament resources; call Services/Actions.
2. Every Teacher/Student resource overrides `getEloquentQuery()` to scope data.
3. Every model has a Shield-generated Policy (extend it only with stricter checks); panels never rely on UI hiding alone.
4. Computed tables (`grades`, `performance_summaries`) are written only by Services/Jobs.
5. Locked periods are enforced in Policies and the Action layer, not only in forms.
6. Use enums instead of magic strings.
7. Use DB transactions for multi-row writes (bulk score entry).
8. `canAccessPanel()` checks the Shield permission `access_{panel}_panel`; never check role names (`hasRole`) for access. Teacher/Student resources use the `ScopesByPermission` trait. The Student panel is read-only for admins because Shield only grants them view permissions there.
9. Every new resource, page, widget, or custom action gets Shield permissions (`shield:generate`) and uses `HasPageShield` / `HasWidgetShield` / `->authorize()`. See `06-roles-and-permissions.md`.

## 4. Tailwind / Theme Setup

```bash
php artisan make:filament-theme admin
php artisan make:filament-theme teacher
php artisan make:filament-theme student
```

Add each theme CSS to `vite.config.js` inputs and register with `->viteTheme()` in the matching panel provider. Keep shared design tokens (colors, fonts) in one imported file, with each panel adding its own accent color.

## 5. Navigation Config

**Admin** (sidebar groups): Academic Setup, Users, Grading Rules, Reports.

**Teacher** (top nav, via `->topNavigation()`): Dashboard, My Classes, Scores, Activities, Attendance, Performance.

**Student** (top nav, via `->topNavigation()`): Dashboard, My Grades, Activities, Attendance, Performance, Profile.

**Profile menu (`->userMenuItems()`):**
- Admin panel: "Teacher Panel", "Student Panel" (visible by `access_teacher_panel` / `access_student_panel`).
- Teacher and Student panels: "Back to Admin Panel" (visible by `access_admin_panel`).
- Shared files: `app/Filament/Shared/Concerns/ScopesByPermission.php` may provide teacher scope helpers (bypass via `view_all_classes`); student resources must separately scope to the signed-in student or a validated admin-selected student. `app/Filament/Student/Pages/SelectStudent.php` is the proposed admin "Viewing as" selector.

Use `$navigationSort` on resources/pages for ordering. Keep top nav to 6-7 items; move extras into `->userMenuItems()`.

## 6. Testing Layout

- **Unit:** grade calculation edge cases (missing scores, excused, zero max score, weights not summing to 100).
- **Feature:** panel access per role, teacher cannot open another teacher's class, student cannot see other students, locked period rejects edits.
- **Browser (optional):** Pest/Dusk smoke test for each panel login.
