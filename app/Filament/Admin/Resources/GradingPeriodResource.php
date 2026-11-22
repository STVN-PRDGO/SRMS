<?php

namespace App\Filament\Admin\Resources;

use App\Actions\Periods\ClosePeriod;
use App\Actions\Periods\OpenPeriod;
use App\Actions\Periods\ReopenPeriod;
use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\GradingPeriodResource\Pages\ManageGradingPeriods;
use App\Models\GradingPeriod;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GradingPeriodResource extends Resource
{
    protected static ?string $model = GradingPeriod::class;

    protected static ?string $navigationLabel = 'Periods';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::School;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_year_id')
                ->relationship('schoolYear', 'name')
                ->required()
                ->preload(),
            TextInput::make('name')
                ->label('Period Name')
                ->placeholder('e.g. 1st Quarter, Midterm')
                ->required()
                ->maxLength(255),
            DatePicker::make('starts_on')
                ->label('Start Date')
                ->native(false)
                ->required(),
            DatePicker::make('ends_on')
                ->label('End Date')
                ->native(false)
                ->afterOrEqual('starts_on')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolYear.name')->label('School Year')->sortable(),
                TextColumn::make('name')->label('Period')->searchable()->sortable(),
                TextColumn::make('starts_on')->date()->sortable(),
                TextColumn::make('ends_on')->date()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
            ])
            ->defaultSort('starts_on', 'desc')
            ->recordActions([
                EditAction::make(),
                Action::make('open')
                    ->icon('heroicon-m-lock-open')
                    ->color('success')
                    ->authorize(fn (): bool => (bool) auth()->user()?->can('update_grading_period_admin'))
                    ->visible(fn (GradingPeriod $record): bool => $record->status->value === 'draft')
                    ->action(function (GradingPeriod $record): void {
                        app(OpenPeriod::class)($record, auth()->user());
                        Notification::make()->title('Period opened')->success()->send();
                    }),
                Action::make('close')
                    ->icon('heroicon-m-lock-closed')
                    ->color('danger')
                    ->authorize('close_grading_period')
                    ->visible(fn (GradingPeriod $record): bool => $record->status->value === 'open')
                    ->requiresConfirmation()
                    ->action(function (GradingPeriod $record): void {
                        app(ClosePeriod::class)($record, auth()->user());
                        Notification::make()->title('Period closed')->success()->send();
                    }),
                Action::make('reopen')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->authorize('reopen_grading_period')
                    ->visible(fn (GradingPeriod $record): bool => $record->status->value === 'closed')
                    ->requiresConfirmation()
                    ->action(function (GradingPeriod $record): void {
                        app(ReopenPeriod::class)($record, auth()->user());
                        Notification::make()->title('Period reopened')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No grading periods')
            ->emptyStateDescription('Create quarters or semesters for the active school year.')
            ->emptyStateIcon('heroicon-o-clock');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradingPeriods::route('/')];
    }
}
