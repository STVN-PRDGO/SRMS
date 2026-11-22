<?php

namespace App\Filament\Teacher\Pages;

use App\Filament\Teacher\Concerns\ResolvesTaughtStudents;
use App\Models\Student;
use App\Support\PermissionName;
use Filament\Pages\Page;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class MyStudents extends Page
{
    use ResolvesTaughtStudents;
    use WithPagination;

    protected static ?string $navigationLabel = 'My students';

    protected static ?string $title = 'My students';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 2;

    public string $query = '';

    public string $appliedQuery = '';

    public function getPageClasses(): array
    {
        return ['srms-teacher-students'];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->can(PermissionName::for('view_any', 'class_subject', 'teacher'))
            || $user?->can(PermissionName::for('view_any', 'score', 'teacher'));
    }

    /**
     * @return array<string>
     */
    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return [
            static::getRouteName(),
            StudentRecord::getRouteName(),
        ];
    }

    public function applySearch(): void
    {
        $this->appliedQuery = trim($this->query);
        $this->resetPage();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Html::make(fn () => view('filament.teacher.my-students', [
                'students' => $this->students(),
                'appliedQuery' => $this->appliedQuery,
            ])),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Student>
     */
    public function students(): LengthAwarePaginator
    {
        $user = auth()->user();
        $search = str_replace(['%', '_'], ['\\%', '\\_'], $this->appliedQuery);

        return $this->taughtStudentsQuery($user)
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select('students.*')
            ->with([
                'user',
                'section.gradeLevel',
                'section.schoolYear',
                'section.classSubjects.subject',
                'section.classSubjects.teacher',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('students.student_no', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($users) => $users->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('users.name')
            ->paginate(16);
    }
}
