<?php

namespace App\Filament\Shared\Concerns;

use App\Models\ClassSubject;
use Illuminate\Database\Eloquent\Builder;

trait ScopesByPermission
{
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder $query */
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('0 = 1');
        }

        if ($user->can('view_all_classes')) {
            return $query;
        }

        $teacherId = $user->teacher?->id ?? 0;

        if ($query->getModel() instanceof ClassSubject) {
            return $query->where('teacher_id', $teacherId);
        }

        $relation = method_exists($query->getModel(), 'classSubject')
            ? 'classSubject'
            : 'activity.classSubject';

        return $query->whereHas($relation, fn (Builder $classSubject) => $classSubject->where('teacher_id', $teacherId));
    }
}
