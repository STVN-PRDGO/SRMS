# 03 - Database Design

## 1. ERD (simplified)

```
users ──1:1── teachers
  │──1:1── students ──N:1── sections ──N:1── grade_levels
  │
school_years ──1:N── grading_periods
school_years ──1:N── sections

subjects ──1:N── grading_components (weights)
sections + subjects + teachers ──► class_subjects
class_subjects ──1:N── activities ──1:N── scores ──N:1── students
class_subjects ──1:N── attendances ──N:1── students
students + class_subjects + grading_periods ──► grades
students + grading_periods ──► performance_summaries
```

## 2. Tables

### users
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | |
| is_active | boolean | default true |
| must_change_password | boolean | default true for new accounts |
| timestamps | | |

Roles and permissions are stored by Filament Shield / Spatie Permission (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions`). Do not edit these tables directly; use the Shield role editor. See [06-roles-and-permissions.md](06-roles-and-permissions.md).

### teachers
| id | user_id FK unique | employee_no unique | department | contact_no | timestamps |

### students
| id | user_id FK unique | student_no unique | section_id FK | birthdate | guardian_name | guardian_contact | status (enum: active, transferred, graduated) | timestamps |

### school_years
| id | name (e.g., 2026-2027) | starts_on | ends_on | is_current | timestamps |

### grading_periods
| id | school_year_id FK | name (Q1, Q2... or Prelim/Midterm/Final) | starts_on | ends_on | status (draft, open, closed) | timestamps |

### grade_levels
| id | name | order | timestamps |

### sections
| id | grade_level_id FK | school_year_id FK | name | adviser_teacher_id FK null | timestamps |

### subjects
| id | code unique | name | description | timestamps |

### class_subjects
| id | section_id FK | subject_id FK | teacher_id FK | schedule (json, null) | timestamps |
Unique: `(section_id, subject_id)`

### grading_components
| id | subject_id FK null | name (Quiz, Activity, Project, Exam) | weight decimal(5,2) | timestamps |
Rule: weights per subject must sum to 100 (validated in service).

### activities
| id | class_subject_id FK | grading_period_id FK | grading_component_id FK | title | type (enum) | description | max_score decimal(6,2) | due_date | timestamps |

### scores
| id | activity_id FK | student_id FK | score decimal(6,2) null | status (graded, missing, excused) | remarks | graded_by FK users | timestamps |
Unique: `(activity_id, student_id)`

### attendances
| id | class_subject_id FK | student_id FK | date | status (present, late, absent, excused) | remarks | timestamps |
Unique: `(class_subject_id, student_id, date)`

**Open design point:** attendance rows currently have no `grading_period_id`. Define whether period membership is derived from `date` and the period date range, and how overlapping or out-of-range dates are handled.

### grades (computed, derived data)
| id | student_id FK | class_subject_id FK | grading_period_id FK | raw_grade decimal(5,2) | letter | remark | is_locked boolean | computed_at | timestamps |
Unique: `(student_id, class_subject_id, grading_period_id)`

Grades are recalculated by application services/jobs. Clarify whether `is_locked` means a published snapshot that must not be recalculated, or whether closing a period only blocks source edits while still allowing an explicit admin recalculation.

### grade_scales
| id | min_grade | max_grade | letter | remark | timestamps |

### performance_levels
| id | name | min_grade | max_grade | min_attendance null | order | timestamps |

### performance_summaries (computed)
| id | student_id FK | grading_period_id FK | general_average | attendance_rate | activity_completion_rate | performance_level_id FK | trend (up, same, down) | teacher_remarks text null | computed_at | timestamps |
Unique: `(student_id, grading_period_id)`

### audits
Provided by auditing package: who changed which score/grade, old and new values.

## 3. Eloquent Relationships

| Model | Relations |
|-------|-----------|
| User | hasOne Teacher, hasOne Student |
| Student | belongsTo User, Section; hasMany Scores, Attendances, Grades, PerformanceSummaries |
| Teacher | belongsTo User; hasMany ClassSubjects |
| Section | belongsTo GradeLevel, SchoolYear; hasMany Students, ClassSubjects |
| ClassSubject | belongsTo Section, Subject, Teacher; hasMany Activities, Attendances, Grades |
| Activity | belongsTo ClassSubject, GradingPeriod, GradingComponent; hasMany Scores |
| Score | belongsTo Activity, Student |

## 4. Indexes

- `scores (activity_id, student_id)` unique, plus `scores (student_id)` for a student's score list
- `attendances (class_subject_id, student_id, date)` unique, `(student_id, date)`, and `(class_subject_id, date)` for a class roll
- `grades (student_id, class_subject_id, grading_period_id)` unique, `(student_id, grading_period_id)`, and `(class_subject_id, grading_period_id)` for class pages
- `activities (class_subject_id, grading_period_id)`
- `class_subjects (teacher_id)` for teacher scoping
- `students (section_id, status)`
- `performance_summaries (grading_period_id, performance_level_id)` for at-risk and honor lists
- `audits (created_at)` for the audit list

## 4a. Calculation rules

Implemented in `GradeCalculator` and `PerformanceEvaluator`. See "Resolved Decisions" in `05-implementation-plan.md`.

- Missing scores count as zero. Excused scores are excluded. A missing score row is not counted yet.
- Components with nothing countable drop out, and the remaining weights are rescaled to 100.
- `max_score` of zero is invalid and is skipped by the calculator.
- Attendance is matched to a period by date. It affects performance summaries only.
- Activity completion is the share of activities whose score is graded or excused.

## 5. Enums (PHP backed enums in `app/Enums`)

`ActivityType`, `ScoreStatus`, `AttendanceStatus`, `PeriodStatus`, `StudentStatus`, `Trend`

## 6. Seeders and Factories

- Roles & permissions (`RoleAndPermissionSeeder`)
- Default admin and super_admin users (`RoleAndPermissionSeeder`)
- Default grade scale and performance levels (`ReferenceDataSeeder`)
- Comprehensive Demo Data (`DemoDataSeeder`): School Year 2026-2027, 4 grading periods (Q1/Q2 closed, Q3 open, Q4 draft), 2 grade levels, 4 sections, 6 subjects, 4 faculty teachers, 20+ active enrolled students, activities, scores across tiers, daily attendance logs, and calculated grades/performance summaries.
- Eloquent Factories: `UserFactory`, `TeacherFactory`, `StudentFactory`, `SchoolYearFactory`, `GradingPeriodFactory`, `GradeLevelFactory`, `SectionFactory`, `SubjectFactory`, `ClassSubjectFactory`, `GradingComponentFactory`, `ActivityFactory`, `ScoreFactory`, `AttendanceFactory`.
