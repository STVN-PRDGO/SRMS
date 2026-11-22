<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditResource\Pages\ManageAudits;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use OwenIt\Auditing\Models\Audit;

class AuditResource extends Resource
{
    protected static bool $isGloballySearchable = false;

    protected static ?string $model = Audit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event')->badge(),
                TextColumn::make('auditable_type')->label('Record'),
                TextColumn::make('auditable_id'),
                TextColumn::make('user_id')->label('User'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageAudits::route('/')];
    }
}
