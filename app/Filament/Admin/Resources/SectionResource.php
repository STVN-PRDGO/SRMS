<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\SectionResource\Pages\ManageSections;
use App\Models\Section;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::School;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_year_id')
                ->relationship('schoolYear', 'name')
                ->required()
                ->preload(),
            Select::make('grade_level_id')
                ->relationship('gradeLevel', 'name')
                ->required()
                ->preload(),
            TextInput::make('name')
                ->label('Section Name')
                ->placeholder('e.g. Diamond, Ruby, Section A')
                ->required()
                ->maxLength(255),
            Select::make('adviser_teacher_id')
                ->label('Adviser')
                ->relationship('adviser', 'employee_no', fn ($query) => $query->with('user'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->employee_no.($record->user ? ' ('.$record->user->name.')' : ''))
                ->searchable()
                ->preload()
                ->placeholder('Select section adviser (optional)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Section')->searchable()->sortable(),
                TextColumn::make('gradeLevel.name')->label('Grade Level')->sortable(),
                TextColumn::make('schoolYear.name')->label('School Year')->sortable(),
                TextColumn::make('adviser.user.name')->label('Adviser')->default('—')->searchable(),
                TextColumn::make('students_count')->counts('students')->label('Students')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No sections found')
            ->emptyStateDescription('Create class sections and assign advisers.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function getPages(): array
    {
        return ['index' => ManageSections::route('/')];
    }
}
