<?php

namespace App\Filament\Student\Pages;

use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SelectStudent extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Viewing As';

    protected static ?string $title = 'Viewing as a student';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static ?int $navigationSort = 7;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        $permission = static::getPagePermission();

        return $user !== null
            && $user->can('view_as_student')
            && $permission
            && $user->can($permission);
    }

    public function mount(): void
    {
        $this->data = ['student_id' => session('viewing_student_id')];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label('Student')
                ->options(fn () => Student::query()->with('user')->get()->mapWithKeys(
                    fn (Student $student): array => [$student->id => $student->student_no.' · '.$student->user->name],
                ))
                ->searchable()
                ->live()
                ->afterStateUpdated(function (?string $state): void {
                    if ($state && Student::query()->whereKey($state)->exists()) {
                        session(['viewing_student_id' => (int) $state]);
                        Notification::make()->title('Student selected')->success()->send();
                    }
                }),
        ])->statePath('data');
    }
}
