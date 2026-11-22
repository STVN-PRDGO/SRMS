<?php

namespace App\Filament\Teacher\Resources;

use App\Enums\ActivityType;
use App\Filament\Shared\Concerns\ScopesByPermission;
use App\Filament\Teacher\Resources\ActivityResource\Pages\ManageActivities;
use App\Models\Activity;
use App\Models\GradingComponent;
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

    protected static ?string $navigationLabel = 'Activities';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('class_subject_id')
                ->relationship('classSubject', 'id', fn ($query) => $query->visibleTo(auth()->user())->with(['section', 'subject']))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->section->name.' · '.$record->subject->name)
                ->required()
                ->searchable()
                ->preload(),
            Select::make('grading_period_id')->relationship('gradingPeriod', 'name')->required()->preload(),
            Select::make('type')
                ->options(collect(ActivityType::forRecording())->mapWithKeys(
                    fn (ActivityType $type): array => [$type->value => $type->getLabel()]
                )->all())
                ->live()
                ->afterStateUpdated(function (?string $state, callable $set): void {
                    $type = ActivityType::tryFrom((string) $state);
                    $componentId = $type === null
                        ? null
                        : GradingComponent::query()->whereNull('subject_id')->where('name', $type->componentName())->value('id');

                    if ($componentId !== null) {
                        $set('grading_component_id', $componentId);
                    }
                })
                ->required(),
            Select::make('grading_component_id')
                ->label('Component')
                ->relationship('gradingComponent', 'name')
                ->helperText('Filled from the activity type after a grading system is applied.')
                ->required()
                ->preload(),
            TextInput::make('title')
                ->label('Activity Title')
                ->placeholder('e.g. Unit 1 Quiz, Essay 2')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->placeholder('Optional instructions or notes')
                ->columnSpanFull(),
            TextInput::make('max_score')
                ->label('Maximum Possible Score')
                ->numeric()
                ->required()
                ->minValue(0.01)
                ->default(100),
            DatePicker::make('due_date')
                ->label('Due Date')
                ->native(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Activity')->searchable()->sortable()->weight('bold'),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('classSubject.subject.name')->label('Subject')->sortable(),
                TextColumn::make('classSubject.section.name')->label('Section')->sortable(),
                TextColumn::make('gradingPeriod.name')->label('Period')->sortable(),
                TextColumn::make('gradingComponent.name')->label('Component')->sortable(),
                TextColumn::make('max_score')->label('Max Score')->sortable(),
                TextColumn::make('due_date')->date()->label('Due Date')->sortable(),
            ])
            ->defaultSort('due_date', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No activities created')
            ->emptyStateDescription('Create assignments, quizzes, and exams for your classes.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }

    public static function getPages(): array
    {
        return ['index' => ManageActivities::route('/')];
    }
}
