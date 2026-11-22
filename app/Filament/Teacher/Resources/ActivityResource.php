<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\ActivityType;
use App\Filament\Shared\Concerns\ScopesByPermission;
use App\Filament\Teacher\Resources\ActivityResource\Pages\ManageActivities;
use App\Models\Activity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    use ScopesByPermission;

    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_subject_id')
                ->relationship('classSubject', 'id', fn ($query) => $query->visibleTo(auth()->user()))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->section->name.' · '.$record->subject->name)
                ->required()
                ->searchable()
                ->preload(),
            Select::make('grading_period_id')->relationship('gradingPeriod', 'name')->required(),
            Select::make('grading_component_id')->relationship('gradingComponent', 'name')->required(),
            TextInput::make('title')->required(),
            Select::make('type')->options(ActivityType::class)->required(),
            Textarea::make('description'),
            TextInput::make('max_score')->numeric()->required()->minValue(0.01),
            DatePicker::make('due_date'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('classSubject.subject.name')->label('Subject'),
                TextColumn::make('gradingPeriod.name')->label('Period'),
                TextColumn::make('gradingComponent.name')->label('Component'),
                TextColumn::make('max_score'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageActivities::route('/')];
    }
}
