<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\GradingComponentResource\Pages\ManageGradingComponents;
use App\Models\GradingComponent;
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

class GradingComponentResource extends Resource
{
    protected static ?string $model = GradingComponent::class;

    protected static ?string $navigationLabel = 'Components';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Grading;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->relationship('subject', 'name')
                ->placeholder('Default for all subjects')
                ->searchable()
                ->preload(),
            TextInput::make('name')
                ->label('Component Name')
                ->placeholder('e.g. Written Works, Performance Tasks, Exam')
                ->required()
                ->maxLength(255),
            TextInput::make('weight')
                ->label('Weight Percentage')
                ->numeric()
                ->required()
                ->minValue(0.01)
                ->maxValue(100)
                ->suffix('%'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.name')->label('Subject')->placeholder('Global Default')->sortable(),
                TextColumn::make('name')->label('Component')->sortable()->searchable(),
                TextColumn::make('weight')->label('Weight')->suffix('%')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No grading components')
            ->emptyStateDescription('Create grading components (e.g. Quizzes, Exams) and define their weights.')
            ->emptyStateIcon('heroicon-o-scale');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradingComponents::route('/')];
    }
}
