<?php

namespace App\Filament\Student\Resources;

use App\Filament\Shared\Concerns\ScopesToStudent;
use App\Filament\Student\Resources\AttendanceResource\Pages\ListAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    use ScopesToStudent;

    protected static ?string $model = Attendance::class;

    protected static ?string $navigationLabel = 'Attendance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('date')->date(),
            TextColumn::make('classSubject.subject.name')->label('Subject'),
            TextColumn::make('status')->badge(),
            TextColumn::make('remarks'),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListAttendances::route('/')];
    }
}
