<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\PerformanceLevelResource\Pages\ManagePerformanceLevels;
use App\Models\PerformanceLevel;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformanceLevelResource extends Resource
{
    protected static ?string $model = PerformanceLevel::class;

    protected static ?string $navigationLabel = 'Performance levels';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Grading;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Level Name')
                ->placeholder('e.g. Outstanding, Very Satisfactory')
                ->required()
                ->maxLength(255),
            TextInput::make('min_grade')
                ->label('Minimum Grade')
                ->numeric()
                ->required()
                ->suffix('%'),
            TextInput::make('max_grade')
                ->label('Maximum Grade')
                ->numeric()
                ->required()
                ->suffix('%'),
            TextInput::make('min_attendance')
                ->label('Min Attendance Gate')
                ->numeric()
                ->suffix('%')
                ->helperText('Leave empty if attendance does not gate this level.'),
            TextInput::make('order')
                ->label('Rank / Order')
                ->numeric()
                ->required()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->label('Order')->sortable(),
                TextColumn::make('name')->label('Performance Level')->searchable()->sortable()->weight('bold'),
                TextColumn::make('min_grade')->label('Min Grade')->suffix('%')->sortable(),
                TextColumn::make('max_grade')->label('Max Grade')->suffix('%')->sortable(),
                TextColumn::make('min_attendance')->label('Min Attendance')->suffix('%')->default('—')->sortable(),
            ])
            ->defaultSort('order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No performance levels')
            ->emptyStateDescription('Create performance benchmark brackets.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }

    public static function getPages(): array
    {
        return ['index' => ManagePerformanceLevels::route('/')];
    }
}
