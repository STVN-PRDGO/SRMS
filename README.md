# Student Records Management System (SRMS)

A web application where **teachers input student records** (grades, activities, attendance) and the system **calculates grades and determines performance** per student. Built with **Laravel 13, Filament 5, and Tailwind CSS**.

## Panels & Analytical Dashboards

| Panel | Path | Users | Navigation | Analytical Features |
|---|---|---|---|---|
| **Admin** | `/admin` | Admin | Sidebar | **Full Analytical Dashboard**: Enrolled students, faculty count, section count, school GPA average with sparklines, overall attendance rate, Performance Level Distribution chart, Section Averages comparison chart, and Attendance Health doughnut chart. Manage users, classes, subjects, school years, grading rules, and reports. Cross-panel links in profile menu. |
| **Teacher** | `/teacher` | Teachers | **Top** | **Analytical Teacher Dashboard**: KPI stats banner (assigned classes, student count, class average grade, pending grading tasks, at-risk count), Class Subject Performance comparison chart, Letter Grade Distribution chart (A-F), and quick score entry grid. |
| **Student** | `/student` | Students | **Top** | **Analytical Student Dashboard**: General Average KPI, Performance Level badge (Outstanding, Very Satisfactory, etc.), Attendance rate %, Activity completion rate %, Subject Grade Breakdown chart, and Quarterly GPA Progression line chart. Downloadable report card. |

## Documentation Index

| File | Contents |
|---|---|
| [01-system-architecture.md](01-system-architecture.md) | Layers, components, technical decisions, and security |
| [03-database-design.md](03-database-design.md) | ERD, tables, relationships, indexes, query scopes, and factories |
| [04-project-structure.md](04-project-structure.md) | Folder layout, naming conventions, analytical widgets, and code rules |
| [05-implementation-plan.md](05-implementation-plan.md) | Phases, tasks, acceptance criteria, and resolved decisions |
| [06-roles-and-permissions.md](06-roles-and-permissions.md) | Filament Shield setup, roles, permission matrix, and rules |

## Tech Stack

- **PHP** 8.4+, **Laravel** 13
- **Filament** 5.x (three panels, multi-panel setup with ChartWidget analytics)
- **Tailwind CSS** (Filament custom theme per panel)
- **SQLite** for local development; **MySQL 8** / PostgreSQL in production
- **Filament Shield** (`bezhansalleh/filament-shield`, built on Spatie Laravel Permission)
- **PHPUnit** (Laravel test runner)

## Local Login & Demo Credentials

After running `php artisan migrate --seed`:

| Role | Email | Password | Panel | Description |
|---|---|---|---|---|
| **School Admin** | `smprodigo@up.edu.ph` | `smprodigo@up.edu.ph` | `/admin` | Primary administrative account with cross-panel navigation |
| **Super Admin** | `superadmin@example.com` | `password` | `/admin` | Break-glass system owner account |
| **Teacher** | `teacher@example.com` | `password` | `/teacher` | Prof. Elena Vance (Math & CS classes with active student records) |
| **Student** | `student@example.com` | `password` | `/student` | Alex Cruz (Grade 7 student with comprehensive Q1-Q3 grades & attendance) |

## Quick Start

```bash
composer install
cp .env.example .env   # created on first install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```
