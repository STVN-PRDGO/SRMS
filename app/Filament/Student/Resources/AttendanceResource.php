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
        return $table
            ->columns([
                TextColumn::make('date')->date()->label('Date')->sortable(),
                TextColumn::make('classSubject.subject.name')->label('Subject')->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('remarks')->label('Remarks')->default('—'),
            ])
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('No attendance records')
            ->emptyStateDescription('Your daily attendance records will be logged here.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public static function getPages(): array
    {
        return ['index' => ListAttendances::route('/')];
    }
}
