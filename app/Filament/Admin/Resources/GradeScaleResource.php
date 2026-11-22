<?php

namespace App\Filament\Admin\Resources;

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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|\UnitEnum|null $navigationGroup = 'Grading Rules';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('min_grade')->numeric()->required(),
            TextInput::make('max_grade')->numeric()->required(),
            TextInput::make('letter')->required()->maxLength(8),
            TextInput::make('remark')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_grade'),
                TextColumn::make('max_grade'),
                TextColumn::make('letter'),
                TextColumn::make('remark'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradeScales::route('/')];
    }
}
