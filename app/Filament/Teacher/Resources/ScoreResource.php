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

    protected static ?string $model = Score::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.title')->label('Activity'),
                TextColumn::make('student.user.name')->label('Student'),
                TextColumn::make('score'),
                TextColumn::make('status')->badge(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageScores::route('/')];
    }
}
