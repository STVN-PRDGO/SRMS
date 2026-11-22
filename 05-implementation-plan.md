# 05 - Implementation Plan

Estimated total: ~8 weeks for one developer. Adjust to team size.

Phases 0–4 are implemented in this repository. Phase 5 has the admin reports, class-record export, audit viewer, and access tests. Deployment, a printed user guide, and a PDF library are still open.

## Phase 0 - Setup (Week 1)

- [ ] Create Laravel project, configure `.env`, database
- [ ] Install Filament, Filament Shield (includes Spatie Permission), auditing package, Pest
- [ ] Create three panels (admin, teacher, student)
- [ ] Enable `->topNavigation()` on teacher and student panels
- [ ] Set up Tailwind themes per panel
- [ ] Configure Shield per `06-roles-and-permissions.md`: plugin on admin panel, custom permissions, `shield:generate` for each panel, spike to confirm all panels' permissions are editable in the role editor
- [ ] First-install-only role seeder (admin, teacher, student defaults) and `super_admin` user; seeder must not overwrite later Shield changes
- [ ] `canAccessPanel()` gated by Shield permissions `access_{panel}_panel`
- [ ] Profile menu items ("Teacher Panel", "Student Panel", "Back to Admin Panel") shown via `can('access_*_panel')`

**Done when:** teachers and students log into only their own panel; admin can reach all three from the profile menu; teacher/student show top nav; revoking or granting a permission in the Shield role editor immediately changes access.

## Phase 1 - Core Data & Admin (Weeks 2-3)

- [ ] Migrations and models for all tables (see 03-database-design)
- [ ] Admin resources: SchoolYear, GradingPeriod, GradeLevel, Section, Subject
- [ ] Admin resources: User, Teacher, Student (account creation + role assignment)
- [ ] ClassSubject assignment (teacher ↔ section ↔ subject)
- [ ] GradingComponent weights with sum-to-100 validation
- [ ] GradeScale and PerformanceLevel management
- [ ] CSV import for students

**Done when:** admin can fully configure a school year and enroll students.

## Phase 2 - Teacher Panel (Weeks 3-5)

- [ ] Query scoping and Shield-based policies for teacher data (`ScopesByPermission` trait; `view_all_classes` sees all classes)
- [ ] Shield permissions for custom actions (`close_grading_period`, `export_class_record`, ...)
- [ ] My Classes (roster by section-subject)
- [ ] Activity CRUD
- [ ] Score entry grid (bulk, validated, transactional)
- [ ] Attendance entry
- [ ] Dashboard widgets (class average, pending grading, at-risk students)

**Done when:** a teacher can create an activity, enter scores, and see only their classes.

## Phase 3 - Calculation Engine (Weeks 5-6)

- [ ] `GradeCalculator` service with unit tests
- [ ] `PerformanceEvaluator` service with unit tests
- [ ] Observers + queued recalculation job
- [ ] Teacher Performance page (per-student breakdown, trend, remarks)
- [ ] Period close/reopen actions with locking

**Done when:** saving a score updates the grade and performance level correctly; closed periods reject edits.

## Phase 4 - Student Panel (Week 6-7)

- [ ] Student-scoped resources and policies
- [ ] My Grades (component breakdown per period)
- [ ] My Activities, My Attendance
- [ ] Performance overview with trend chart
- [ ] Profile and password change
- [ ] Admin "Viewing as" student selector (read-only mode, banner, hidden write actions)
- [ ] Report card PDF download (closed periods only)

**Done when:** a student sees only their own data and can download a report card.

## Phase 5 - Reports, Hardening, Release (Weeks 7-8)

- [x] Analytical dashboards (Admin KPI overview, Performance Distribution, Section Comparison, Attendance Health, Teacher Class Performance & Grade Distribution, Student Subject Breakdown & Quarterly Progression)
- [x] Dedicated `AnalyticsService` layer encapsulating multi-model statistics and charts
- [x] Robust `PerformanceEvaluator` tier cascade logic and fallback mechanisms
- [x] Complete model factory suite and comprehensive `DemoDataSeeder` with realistic school year, historical and active quarters, score distributions, and attendance logs
- [x] Admin reports (section averages, honor list, at-risk list)
- [x] Teacher class record export (Excel)
- [x] Audit log viewer
- [x] Feature tests for all policies, panel access, analytics, and cascading evaluation
- [x] Permission-reflection tests (grant/revoke in Shield changes access, nav, and buttons immediately; seeder rerun keeps UI changes)
- [x] Performance pass (eager loading, indexes, caching)
- [ ] Deployment: queue worker, scheduler, backups, HTTPS
- [ ] User guide per role

**Done when:** test suite passes, UAT signed off by one teacher and one admin.

## Milestones

| # | Milestone | End of |
|---|-----------|--------|
| M1 | Panels running, roles working | Week 1 |
| M2 | Admin setup complete | Week 3 |
| M3 | Teacher can record scores | Week 5 |
| M4 | Auto grades and performance | Week 6 |
| M5 | Student panel live | Week 7 |
| M6 | Release candidate | Week 8 |

## Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| Grading rules differ per school | Keep weights, scales, and levels in DB tables, not code |
| Teachers edit closed grades | Policy + Action-level locking, audit log |
| Slow bulk score entry | Transactions, batched upserts, queued recalculation |
| Data leaks between users | Policies + scoped queries + feature tests |
| Top nav overflow | Limit items, use user menu for secondary links |
| Permissions drift between code and Shield | No role-name checks, `can()` only; seeders run once; reflection tests in CI |

## Resolved Decisions

These choices close the open questions below for the current build:

1. Grades are percentages. A missing score counts as zero. An excused score is left out. A score that has not been entered yet is left out. Component weights that are unused, or that do not sum to 100, are rescaled across the components that have countable scores. Results round to two decimals. `max_score` must be greater than zero.
2. Periods are named records. Their dates cannot overlap inside one school year. Status moves draft, then open, then closed.
3. Attendance belongs to the period whose dates contain that day. It changes the performance summary only, not the numeric grade. Present and late count as attended. Excused is left out.
4. An activity is complete when its score is graded or excused. There is no separate submission table.
5. No guardian portal.
6. Accounts are created by an administrator, including CSV import.
7. A report card can be downloaded for a closed period as HTML. A PDF library was not added.
8. Closing a period locks computed grades and blocks source edits. Reopening unlocks them and recalculates.

## Open Questions

The decisions above are the working rules. Change them in the services if a school needs a different formula. Related calculation and schema gaps are listed in `03-database-design.md`.

1. Grading system: percentage, GPA, or letter-only? Define missing/excused score treatment, component aggregation, and rounding.
2. Number and names of grading periods per year (quarters vs terms), and whether periods can overlap?
3. How is attendance assigned to a grading period, and does it affect grades or only performance summaries?
4. How is activity completion recorded: a score status, a submission record, or another model?
5. Do parents/guardians need a portal later?
6. Is student self-registration needed, or admin-created accounts only?
7. What report card format is required, and can reports be regenerated after a period is closed?
