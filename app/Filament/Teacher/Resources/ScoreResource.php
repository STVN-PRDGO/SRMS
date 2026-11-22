<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Shared\Concerns\ScopesByPermission;
use App\Filament\Teacher\Resources\ScoreResource\Pages\ManageScores;
use App\Models\Score;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScoreResource extends Resource
{
    use ScopesByPermission;

    protected static bool $isGloballySearchable = false;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Score::class;

    protected static ?string $navigationLabel = 'Score log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.title')->label('Activity')->searchable()->sortable(),
                TextColumn::make('student.user.name')->label('Student')->searchable()->sortable(),
                TextColumn::make('score')->label('Earned Score')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('remarks')->label('Remarks')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->label('Last Updated')->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('No scores recorded')
            ->emptyStateDescription('Scores entered using Enter Scores will appear in this log.')
            ->emptyStateIcon('heroicon-o-pencil-square');
    }

    public static function getPages(): array
    {
        return ['index' => ManageScores::route('/')];
    }
}
