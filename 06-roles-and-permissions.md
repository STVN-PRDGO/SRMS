# 06 - Roles & Permissions (Filament Shield)

**Principle: Filament Shield is the single source of truth for permissions.** Whatever is changed in the Shield role editor must take effect in the app immediately, in all three panels. Nothing in code may override, duplicate, or reset what Shield stores.

Shield (`bezhansalleh/filament-shield`) is built on Spatie Laravel Permission, so roles and permissions live in the standard `roles`, `permissions`, `model_has_roles`, and `role_has_permissions` tables.

> Config key names and artisan flags below follow Shield 3.x. Confirm them against the installed version's docs during the Phase 0 setup.

## 1. Roles

| Role | Who | Notes |
|------|-----|-------|
| `super_admin` | Developer / system owner only | Shield default. Bypasses the gate. Break-glass account; never assign to school staff. |
| `admin` | School administrators | Fully **permission-driven**. No bypass, so anything can be restricted in Shield. |
| `teacher` | Teachers | Permission-driven, plus data ownership (own classes only). |
| `student` | Students | Permission-driven, plus data ownership (own records only). |

`panel_user` (Shield's built-in role) is **disabled**; panel access uses explicit permissions below.

## 2. Installation & Setup

```bash
composer require bezhansalleh/filament-shield
php artisan vendor:publish --tag="filament-shield-config"
php artisan shield:setup                      # migrations + config wiring
php artisan shield:install admin              # registers the plugin on the admin panel
php artisan shield:generate --all --panel=admin
php artisan shield:generate --all --panel=teacher
php artisan shield:generate --all --panel=student
php artisan shield:super-admin                # creates the super_admin user
```

Add `HasRoles` (Spatie) to `User`, register the plugin in `AdminPanelProvider`:

```php
->plugin(\BezhanSalleh\FilamentShield\FilamentShieldPlugin::make())
```

The role editor lives in the Admin panel (Roles resource), available to anyone granted the `view_any_role` / `update_role` permissions.

**Phase 0 spike (verify):** confirm the role editor in the Admin panel lists Teacher-panel and Student-panel resources. If it only lists the current panel's entities, also register the plugin on the other panels (hidden from their navigation) so every permission is editable in one place.

## 3. Shield Config (`config/filament-shield.php`)

```php
'super_admin' => [
    'enabled' => true,
    'name' => 'super_admin',
    'define_via_gate' => false,
    'intercept_gate' => 'before',
],
'panel_user' => ['enabled' => false],

'entities' => [
    'pages' => true,
    'widgets' => true,
    'resources' => true,
    'custom_permissions' => true,
],

'custom_permissions' => [
    // panel access
    'access_admin_panel',
    'access_teacher_panel',
    'access_student_panel',
    // scope / mode
    'view_all_classes',          // see every class (not just own)
    'view_as_student',           // "Viewing as" selector in Student panel
    // actions
    'close_grading_period',
    'reopen_grading_period',
    'export_class_record',
    'download_report_card',
],
```

Resource permissions use Shield's standard prefixes (`view`, `view_any`, `create`, `update`, `delete`, `delete_any`, and so on), e.g. `view_any_score`, `update_activity`. Exact string format depends on the Shield config.

## 4. Default Permission Matrix (initial seed only)

| Permission group | admin | teacher | student |
|------------------|:-----:|:-------:|:-------:|
| `access_admin_panel` | Yes | - | - |
| `access_teacher_panel` | Yes | Yes | - |
| `access_student_panel` | Yes | - | Yes |
| Admin-panel resources (User, Teacher, Student, SchoolYear, GradingPeriod, GradeLevel, Section, Subject, ClassSubject, GradingComponent, GradeScale, PerformanceLevel, Role) | All | - | - |
| Teacher-panel resources (MyClass, Activity, Score, Attendance) | All | All (own classes only) | - |
| Student-panel resources (MyGrade, MyActivity, MyAttendance) | `view`, `view_any` only | - | `view`, `view_any` |
| `view_all_classes` | Yes | - | - |
| `view_as_student` | Yes | - | - |
| `close_grading_period` | Yes | Yes | - |
| `reopen_grading_period` | Yes | - | - |
| `export_class_record` | Yes | Yes | - |
| `download_report_card` | Yes | - | Yes |
| Admin Analytical Widgets (`admin_overview_stats_widget`, `grade_distribution_chart_widget`, `section_performance_chart_widget`, `attendance_health_chart_widget`) | Yes | - | - |
| Teacher Analytical Widgets (`teacher_overview_stats_widget`, `teacher_class_performance_chart_widget`, `teacher_grade_distribution_chart_widget`, `class_average_widget`, `pending_grading_widget`, `at_risk_widget`) | Yes | Yes | - |
| Student Analytical Widgets (`student_overview_stats_widget`, `student_subject_breakdown_chart_widget`, `performance_trend_widget`) | Yes | - | Yes |

This matrix is the **starting point**. After the first deploy, the Shield UI owns it.

## 5. How Changes in Shield Reflect in the App

Every access decision must go through `can()` so Spatie/Shield is consulted at request time.

| Where | How it follows Shield |
|-------|-----------------------|
| Panel access | `User::canAccessPanel()` returns `$this->can("access_{$panel->getId()}_panel")` |
| Profile menu links | `->visible(fn () => auth()->user()->can('access_teacher_panel'))` etc. |
| Resources (CRUD, navigation, buttons) | Shield-generated Policies call `$user->can('<action>_<resource>')`; Filament hides nav items, buttons, and routes accordingly |
| Custom pages | `use HasPageShield;` |
| Widgets | `use HasWidgetShield;` |
| Custom actions | `->authorize('close_grading_period')` or `->visible(fn () => auth()->user()->can('close_grading_period'))` |
| Scope bypass | `ScopesByPermission` trait: `can('view_all_classes')` skips teacher ownership scope |
| Admin viewing Student panel | Read-only because admin is only granted `view`/`view_any` there; no special code |

### Policies: permission first, rules on top

Shield generates the policy. We may **add** ownership and period-lock checks, but never remove or replace the permission check.

```php
public function update(User $user, Score $score): bool
{
    return $user->can('update_score')                       // Shield-controlled
        && $score->classSubject->isManageableBy($user)      // ownership, or can('view_all_classes')
        && ! $score->activity->gradingPeriod->isClosed();   // period lock
}
```

### Scope helpers

Teacher and student ownership scopes are different and must not share an unconditional query clause. A shared concern can provide helpers, but each resource applies the scope appropriate to its data:

- Teacher resources: constrain class-subject ownership unless the user has `view_all_classes`.
- Student resources: constrain to the authenticated student's ID. In admin viewing mode, use only a validated `viewing_student_id` selected through the authorized selector.

The following example is teacher-only; it is not a complete implementation for Student-panel resources.

```php
trait ScopesByPermission
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->can('view_all_classes')) {
            return $query;
        }

        return $query->whereHas('classSubject', fn ($q) => $q->where('teacher_id', $user->teacher?->id));
    }
}
```

## 6. Rules to Keep Shield Authoritative

1. **No role-name checks** (`hasRole('admin')`, `@role`, `role:` middleware) for access decisions. Use permissions only. (The only role-name usage allowed is in seeders and tests.)
2. **No hardcoded permission lists in policies** that bypass the generated `can()` calls.
3. **Seeders must not overwrite UI changes.** Role seeding runs on first install only (create the role and assign defaults only if the role did not exist). Never call `syncPermissions()` on existing roles in deploy scripts.

   ```php
   $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
   if ($teacher->wasRecentlyCreated) {
       $teacher->syncPermissions($defaultTeacherPermissions);
   }
   ```
4. **No custom caching** of permission results. Spatie's cache is flushed automatically when Shield saves a role; after any manual DB or seeder change run `php artisan permission:cache-reset`.
5. **New resource, page, widget, or custom action = new permissions.** Run `php artisan shield:generate --all --panel=<panel>`, then grant them in the Shield UI. Update the matrix in section 4 if it should be part of the default seed.
6. Do not edit Shield-generated policies to loosen access; extend only by adding stricter conditions.
7. Admin-panel Roles resource itself is protected by Shield permissions (`view_any_role`, `update_role`, ...), granted to `admin` by default.

## 7. Tests Required

Using Pest, grant and revoke permissions on a role at runtime, call `app(PermissionRegistrar::class)->forgetCachedPermissions()`, and assert:

- Revoking `update_score` from `teacher` returns 403 on score edit and hides the action.
- Granting `view_any_activity` to `student` shows the nav item; revoking removes it.
- Revoking `access_teacher_panel` from `admin` blocks `/teacher` and removes the profile-menu link.
- Granting the actual create permission generated for the relevant Student-panel resource to `admin` enables writes, proving read-only is permission-driven, not hardcoded. Confirm the permission identifier with the installed Shield version; do not assume a spelling here.
- Teacher without `view_all_classes` sees only own classes; with it, sees all.
- Seeder rerun does not reset a permission changed after install.
