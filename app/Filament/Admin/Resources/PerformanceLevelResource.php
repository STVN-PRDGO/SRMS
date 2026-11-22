<?php

namespace App\Filament\Admin\Resources;

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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Grading Rules';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('min_grade')->numeric()->required(),
            TextInput::make('max_grade')->numeric()->required(),
            TextInput::make('min_attendance')->numeric()->helperText('Leave empty if attendance does not gate this level.'),
            TextInput::make('order')->numeric()->required()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('name'),
                TextColumn::make('min_grade'),
                TextColumn::make('max_grade'),
                TextColumn::make('min_attendance'),
            ])
            ->defaultSort('order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePerformanceLevels::route('/')];
    }
}
