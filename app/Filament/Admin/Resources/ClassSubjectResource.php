<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClassSubjectResource\Pages\ManageClassSubjects;
use App\Models\ClassSubject;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassSubjectResource extends Resource
{
    protected static ?string $model = ClassSubject::class;

    protected static ?string $navigationLabel = 'Class Subjects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('section_id')->relationship('section', 'name')->required()->searchable()->preload(),
            Select::make('subject_id')->relationship('subject', 'name')->required()->searchable()->preload(),
            Select::make('teacher_id')->relationship('teacher', 'employee_no')->required()->searchable()->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('section.name')->label('Section'),
                TextColumn::make('subject.name')->label('Subject'),
                TextColumn::make('teacher.employee_no')->label('Teacher'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageClassSubjects::route('/')];
    }
}
