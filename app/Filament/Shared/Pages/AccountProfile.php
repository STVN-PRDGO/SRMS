<?php

namespace App\Filament\Shared\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rules\Password;

abstract class AccountProfile extends Page
{
    use HasPageShield;

    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'Profile';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 90;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => null,
            'password_confirmation' => null,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->disabled()->dehydrated(false),
                TextInput::make('password')->password()->revealable(),
                TextInput::make('password_confirmation')->password(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save profile')
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        $user = auth()->user();
        $data = validator($this->data, [
            'name' => ['required', 'string', 'max:255'],
            'password' => [$user->must_change_password ? 'required' : 'nullable', 'confirmed', Password::min(8)],
        ])->validate();

        $user->name = $data['name'];

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
            $user->must_change_password = false;
        }

        $user->save();

        Notification::make()->title('Profile saved')->success()->send();
    }
}
