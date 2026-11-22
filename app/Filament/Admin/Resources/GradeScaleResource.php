<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\GradeScaleResource\Pages\ManageGradeScales;
use App\Models\GradeScale;
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

class GradeScaleResource extends Resource
{
    protected static ?string $model = GradeScale::class;

    protected static ?string $navigationLabel = 'Grade scales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Grading;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('min_grade')
                ->label('Minimum Grade')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required()
                ->suffix('%'),
            TextInput::make('max_grade')
                ->label('Maximum Grade')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->required()
                ->suffix('%'),
            TextInput::make('letter')
                ->label('Letter / Equivalent')
                ->required()
                ->maxLength(8),
            TextInput::make('remark')
                ->label('Qualitative Remark')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_grade')->label('Min Grade')->suffix('%')->sortable(),
                TextColumn::make('max_grade')->label('Max Grade')->suffix('%')->sortable(),
                TextColumn::make('letter')->label('Letter / Equivalent')->sortable()->weight('bold'),
                TextColumn::make('remark')->label('Remark')->sortable(),
            ])
            ->defaultSort('min_grade', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No grading scale rules')
            ->emptyStateDescription('Define letter conversions for numerical averages.')
            ->emptyStateIcon('heroicon-o-scale');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradeScales::route('/')];
    }
}
