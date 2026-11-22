<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\GradeLevelResource\Pages\ManageGradeLevels;
use App\Models\GradeLevel;
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

class GradeLevelResource extends Resource
{
    protected static ?string $model = GradeLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::School;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Grade Level Name')
                ->placeholder('e.g. Grade 7, Grade 11')
                ->required()
                ->maxLength(255),
            TextInput::make('order')
                ->label('Order / Sequence')
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
                TextColumn::make('name')->label('Grade Level')->searchable()->sortable(),
                TextColumn::make('sections_count')->counts('sections')->label('Sections')->sortable(),
            ])
            ->defaultSort('order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No grade levels found')
            ->emptyStateDescription('Define year levels for sections.')
            ->emptyStateIcon('heroicon-o-queue-list');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradeLevels::route('/')];
    }
}
