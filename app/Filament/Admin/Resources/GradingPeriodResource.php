<?php

namespace App\Filament\Admin\Resources;

use App\Actions\Periods\ClosePeriod;
use App\Actions\Periods\OpenPeriod;
use App\Actions\Periods\ReopenPeriod;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Academic Setup';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_year_id')->relationship('schoolYear', 'name')->required(),
            TextInput::make('name')->required(),
            DatePicker::make('starts_on')->required(),
            DatePicker::make('ends_on')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schoolYear.name')->label('School year'),
                TextColumn::make('name')->searchable(),
                TextColumn::make('starts_on')->date(),
                TextColumn::make('ends_on')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('open')
                    ->authorize(fn (): bool => (bool) auth()->user()?->can('update_grading_period_admin'))
                    ->visible(fn (GradingPeriod $record): bool => $record->status->value === 'draft')
                    ->action(function (GradingPeriod $record): void {
                        app(OpenPeriod::class)($record, auth()->user());
                        Notification::make()->title('Period opened')->success()->send();
                    }),
                Action::make('close')
                    ->authorize('close_grading_period')
                    ->visible(fn (GradingPeriod $record): bool => $record->status->value === 'open')
                    ->requiresConfirmation()
                    ->action(function (GradingPeriod $record): void {
                        app(ClosePeriod::class)($record, auth()->user());
                        Notification::make()->title('Period closed')->success()->send();
                    }),
                Action::make('reopen')
                    ->authorize('reopen_grading_period')
                    ->visible(fn (GradingPeriod $record): bool => $record->isClosed())
                    ->requiresConfirmation()
                    ->action(function (GradingPeriod $record): void {
                        app(ReopenPeriod::class)($record, auth()->user());
                        Notification::make()->title('Period reopened')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageGradingPeriods::route('/')];
    }
}
