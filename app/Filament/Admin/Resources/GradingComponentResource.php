<?php

namespace App\Filament\Admin\Resources;

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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|\UnitEnum|null $navigationGroup = 'Grading Rules';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('subject_id')
                ->relationship('subject', 'name')
                ->placeholder('Default for every subject')
                ->searchable(),
            TextInput::make('name')->required(),
            TextInput::make('weight')->numeric()->required()->minValue(0.01)->maxValue(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.name')->label('Subject')->placeholder('Default'),
                TextColumn::make('name'),
                TextColumn::make('weight'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradingComponents::route('/')];
    }
}
