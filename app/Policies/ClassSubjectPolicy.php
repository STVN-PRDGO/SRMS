<?php

namespace App\Policies;

use App\Models\ClassSubject;
use App\Models\User;

class ClassSubjectPolicy extends PanelPolicy
{
    protected function subject(): string
    {
        return 'class_subject';
    }

    public function view(User $user, mixed $record): bool
    {
        if (! parent::view($user, $record)) {
            return false;
        }

        if ($this->panel() === 'teacher' && $record instanceof ClassSubject) {
            return $record->isManageableBy($user);
        }

        return true;
    }

    public function update(User $user, mixed $record): bool
    {
        if (! parent::update($user, $record)) {
            return false;
        }

        if ($this->panel() === 'teacher' && $record instanceof ClassSubject) {
            return $record->isManageableBy($user);
        }

        return $this->panel() === 'admin';
    }
}
