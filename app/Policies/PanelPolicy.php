<?php

namespace App\Policies;

use App\Models\User;
use App\Support\PermissionName;
use Filament\Facades\Filament;

abstract class PanelPolicy
{
    abstract protected function subject(): string;

    protected function panel(): string
    {
        return Filament::getCurrentPanel()?->getId() ?? 'admin';
    }

    protected function allows(User $user, string $ability): bool
    {
        return $user->can(PermissionName::for($ability, $this->subject(), $this->panel()));
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'viewAny');
    }

    public function view(User $user, mixed $record): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, mixed $record): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, mixed $record): bool
    {
        return $this->allows($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, 'deleteAny');
    }

    public function restore(User $user, mixed $record): bool
    {
        return $this->allows($user, 'restore');
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, 'restoreAny');
    }

    public function forceDelete(User $user, mixed $record): bool
    {
        return $this->allows($user, 'forceDelete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allows($user, 'forceDeleteAny');
    }

    public function replicate(User $user, mixed $record): bool
    {
        return $this->allows($user, 'replicate');
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, 'reorder');
    }
}
