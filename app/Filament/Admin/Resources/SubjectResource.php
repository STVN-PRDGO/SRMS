<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\SubjectResource\Pages\ManageSubjects;
use App\Models\Subject;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Classes;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Subject Code')
                ->placeholder('e.g. MATH101, SCI-7')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),
            TextInput::make('name')
                ->label('Subject Title')
                ->placeholder('e.g. General Mathematics')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label('Description')
                ->placeholder('Optional course overview or description')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->sortable()->weight('bold'),
                TextColumn::make('name')->label('Subject Name')->searchable()->sortable(),
                TextColumn::make('class_subjects_count')->counts('classSubjects')->label('Active Classes')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No subjects found')
            ->emptyStateDescription('Create academic courses and subjects.')
            ->emptyStateIcon('heroicon-o-book-open');
    }

    public static function getPages(): array
    {
        return ['index' => ManageSubjects::route('/')];
    }
}
