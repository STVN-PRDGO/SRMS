<?php

namespace App\Filament\Teacher\Resources;

use App\Actions\Reports\ExportClassRecord;
use App\Filament\Shared\Concerns\ScopesByPermission;
use App\Filament\Teacher\Resources\ClassSubjectResource\Pages\ManageClassSubjects;
use App\Models\ClassSubject;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClassSubjectResource extends Resource
{
    use ScopesByPermission;

    protected static ?string $model = ClassSubject::class;

    protected static ?string $navigationLabel = 'My Classes';

    protected static ?string $modelLabel = 'class';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('section.name')->label('Section')->searchable()->sortable()->weight('bold'),
                TextColumn::make('subject.name')->label('Subject')->searchable()->sortable(),
                TextColumn::make('section.students_count')->label('Enrolled Students')->default(0),
                TextColumn::make('activities_count')->counts('activities')->label('Activities')->sortable(),
            ])
            ->recordActions([
                Action::make('export')
                    ->label('Export Sheet')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (): bool => (bool) auth()->user()?->can('export_class_record'))
                    ->action(function (ClassSubject $record): BinaryFileResponse {
                        $path = app(ExportClassRecord::class)($record, auth()->user());

                        return response()->download($path)->deleteFileAfterSend();
                    }),
            ])
            ->emptyStateHeading('No classes assigned')
            ->emptyStateDescription('Classes assigned to you by the administrator will appear here.')
            ->emptyStateIcon('heroicon-o-rectangle-group');
    }

    public static function getPages(): array
    {
        return ['index' => ManageClassSubjects::route('/')];
    }
}
