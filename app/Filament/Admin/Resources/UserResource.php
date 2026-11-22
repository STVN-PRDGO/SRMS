<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigationGroup;
use App\Filament\Admin\Resources\UserResource\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Accounts';

    protected static ?string $modelLabel = 'account';

    protected static ?string $pluralModelLabel = 'Accounts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Access;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->prefixIcon('heroicon-m-user')
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->prefixIcon('heroicon-m-envelope')
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->prefixIcon('heroicon-m-key')
                ->visibleOn('create'),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->prefixIcon('heroicon-m-shield-check'),
            Toggle::make('is_active')
                ->label('Active Account')
                ->default(true),
            Toggle::make('must_change_password')
                ->label('Must Change Password on Next Login')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('roles.name')->badge(),
                IconColumn::make('is_active')->label('Active')->boolean()->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Create a new system user to assign access and roles.')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
