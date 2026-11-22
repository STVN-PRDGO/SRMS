# 01 - System Architecture

## 1. Goals

- Teachers record student data once; the system derives grades and performance automatically.
- Strict role separation: Admin, Teacher, Student.
- Teachers only see their own classes/subjects; students only see their own data.

## 2. High-Level Architecture

```
                         ┌──────────────────────────┐
                         │        Browser           │
                         │  Tailwind + Livewire UI  │
                         └────────────┬─────────────┘
                                      │ HTTPS
                         ┌────────────▼─────────────┐
                         │   Laravel (HTTP Kernel)  │
                         │  Middleware: auth, role  │
                         └────────────┬─────────────┘
          ┌───────────────────────────┼───────────────────────────┐
┌─────────▼────────┐       ┌──────────▼─────────┐      ┌──────────▼─────────┐
│  Admin Panel     │       │   Teacher Panel    │      │   Student Panel    │
│  /admin          │       │   /teacher         │      │   /student         │
│  Sidebar nav     │       │   TOP nav          │      │   TOP nav          │
└─────────┬────────┘       └──────────┬─────────┘      └──────────┬─────────┘
          └───────────────────────────┼───────────────────────────┘
                         ┌────────────▼─────────────┐
                         │  Filament Resources,     │
                         │  Pages, Widgets, Actions │
                         └────────────┬─────────────┘
                         ┌────────────▼─────────────┐
                         │   Application Layer      │
                         │  Services / Actions      │
                         │  GradeCalculator         │
                         │  PerformanceEvaluator    │
                         └────────────┬─────────────┘
                         ┌────────────▼─────────────┐
                         │  Domain Layer            │
                         │  Eloquent Models,        │
                         │  Policies, Observers,    │
                         │  Enums                   │
                         └────────────┬─────────────┘
                         ┌────────────▼─────────────┐
                         │  Database (MySQL)        │
                         │  Queue / Cache (DB/Redis)│
                         └──────────────────────────┘
```

## 3. Layers

| Layer | Responsibility | Location |
|-------|----------------|----------|
| Presentation | Filament panels, resources, widgets | `app/Filament/{Admin,Teacher,Student}` |
| Application | Business logic: grade computation, performance rating, analytics & metrics, reports | `app/Services`, `app/Actions` |
| Domain | Models, enums, policies, observers | `app/Models`, `app/Enums`, `app/Policies` |
| Infrastructure | DB, queue, cache, mail, storage | `config/`, `database/` |

**Rule:** Filament resources never contain business logic. They call Services/Actions.

## 4. Multi-Panel Design (Filament)

Three panel providers, each with its own path, auth guard (same `users` table, role-gated), and theme.

| Setting | Admin | Teacher | Student |
|---------|-------|---------|---------|
| Provider | `AdminPanelProvider` | `TeacherPanelProvider` | `StudentPanelProvider` |
| Path | `admin` | `teacher` | `student` |
| Navigation | `->sidebarCollapsibleOnDesktop()` | `->topNavigation()` | `->topNavigation()` |
| Access (Shield permission) | `access_admin_panel` | `access_teacher_panel` | `access_student_panel` |
| Discover | `app/Filament/Admin` | `app/Filament/Teacher` | `app/Filament/Student` |

### Top navigation for Teacher and Student

```php
// app/Providers/Filament/TeacherPanelProvider.php
return $panel
    ->id('teacher')
    ->path('teacher')
    ->login()
    ->topNavigation()
    ->maxContentWidth('full')
    ->viteTheme('resources/css/filament/teacher/theme.css')
    ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
    ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
    ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets');
```

The Student panel uses the same `->topNavigation()` call.

### Panel access control

`User` implements `Filament\Models\Contracts\FilamentUser`:

```php
public function canAccessPanel(Panel $panel): bool
{
// Permissions are managed in Filament Shield (see 06-roles-and-permissions.md)
    return $this->can("access_{$panel->getId()}_panel");
}
```

### Admin cross-panel access (profile menu)

Admins can open the Teacher and Student panels from the **profile menu** (user avatar dropdown, top right). Links appear only for users granted the matching `access_*_panel` permission in Shield. The default permission matrix grants the admin role access to all three panels; teachers and students do not receive cross-panel links by default.

```php
// AdminPanelProvider
use Filament\Navigation\MenuItem;

->userMenuItems([
    MenuItem::make()
        ->label('Teacher Panel')
        ->icon('heroicon-o-academic-cap')
        ->url(fn () => Filament::getPanel('teacher')->getUrl())
        ->visible(fn () => auth()->user()?->can('access_teacher_panel')),
    MenuItem::make()
        ->label('Student Panel')
        ->icon('heroicon-o-user-group')
        ->url(fn () => Filament::getPanel('student')->getUrl())
        ->visible(fn () => auth()->user()?->can('access_student_panel')),
])

// TeacherPanelProvider and StudentPanelProvider
->userMenuItems([
    MenuItem::make()
        ->label('Back to Admin Panel')
        ->icon('heroicon-o-arrow-uturn-left')
        ->url(fn () => Filament::getPanel('admin')->getUrl())
        ->visible(fn () => auth()->user()?->can('access_admin_panel')),
])
```

Admins have no `teachers` or `students` record, so each panel needs a rule for them:

| Panel | Admin behavior |
|-------|----------------|
| Teacher | Sees **all** class-subjects, scores, activities, attendance (teacher scope is skipped for users holding the `view_all_classes` permission). Can edit unless the period is closed. |
| Student | **Read-only** (the admin role is granted only `view`/`view_any` on Student-panel resources in Shield). Admin first picks a student (needs `view_as_student`) ("Viewing as: ..." selector, stored in session as `viewing_student_id`), then sees exactly what that student sees. All create/edit/delete actions are hidden. |

A shared `ScopesByPermission` concern (in `app/Filament/Shared/Concerns`) may hold reusable scope helpers, but teacher and student queries require different ownership rules. Teacher resources scope by assigned class-subject unless `view_all_classes` is granted. Student resources scope to the authenticated student's `student_id`; when an authorized admin uses the Student panel, the selected student ID from session must be validated and applied. Every resource must use the correct scope. The panel menu links are convenience only; access is still enforced by `canAccessPanel()`, policies, and scoped queries.

A banner is shown while an admin is inside another panel ("You are viewing the Teacher panel as Admin") with a link back to the admin panel.

## 5. Authorization Model

- **Roles & permissions:** managed with **Filament Shield** (on top of Spatie Permission). Roles: `super_admin` (break-glass), `admin`, `teacher`, `student`. Shield is the single source of truth: changes made in its role editor take effect immediately. Full details in [06-roles-and-permissions.md](06-roles-and-permissions.md).
- **Policies** on every model (generated by Shield; we only add ownership and period-lock checks on top of the permission check, never replace it):
  - Teacher: can manage `Score`/`Activity`/`Attendance` only where `class_subject.teacher_id = auth user's teacher id`.
  - Student: read-only, only rows tied to own `student_id`.
  - Admin: whatever the `admin` role is granted in Shield. Default: full access in the Admin and Teacher panels, view-only in the Student panel (see "Admin cross-panel access").
- **Query scoping:** `getEloquentQuery()` in each Teacher/Student resource filters to owned data (defense in depth, in addition to policies).

## 6. Core Business Components

### GradeCalculator (Service)
Input: student, class-subject, grading period.
Computes weighted grade from score components (e.g., quizzes 20%, activities 20%, exams 40%, project 20%) using weights defined in `grading_components`. Output: numeric grade + letter/remark via `GradeScale`.

### PerformanceEvaluator (Service)
Aggregates grades, attendance rate, and activity completion into a performance level (e.g., Outstanding / Very Satisfactory / Satisfactory / Needs Improvement) and trend vs previous period. Includes tier cascading fallback when attendance restricts high-tier grades.

### AnalyticsService (Service)
Provides multi-model statistical aggregation across panels: admin school KPIs, performance distribution, section comparative averages, attendance distribution, teacher class performance comparison, and student academic overview.

### Recalculation
Observers on `Score`, activity completion/submission, and `Attendance` dispatch recalculation work so grades and performance summaries stay in sync. The exact activity completion model and which changes trigger grade versus performance recalculation must be defined with the data model.

## 7. Cross-Cutting Concerns

| Concern | Approach |
|---------|----------|
| Auth | Laravel auth, Filament login per panel, optional forced password change for new accounts |
| Audit | `owen-it/laravel-auditing` (or activity log) on scores and grades |
| Validation | Form Requests / Filament form rules; score cannot exceed max score |
| Performance | Eager loading, indexes, cached aggregates per period |
| Exports | Filament export actions (PDF report card, Excel class record) |
| Testing | Pest: unit (calculator), feature (policies, panels) |
| Deployment | Nginx + PHP-FPM, queue worker (Supervisor), scheduled tasks |

## 8. Security Checklist

- Role middleware + policies + query scoping (three layers)
- No student can enumerate other students' IDs (scoped queries return 404)
- Locked grading periods: scores cannot be edited after period is closed (admin can reopen)
- CSRF, rate-limited login, hashed passwords, HTTPS only
- Audit log for grade changes
