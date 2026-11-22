<?php

namespace App\Filament\Shared\Concerns;

use App\Models\Activity;
use App\Support\StudentContext;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToStudent
{
    public static function getEloquentQuery(): Builder
    {
        /** @var Builder $query */
        $query = parent::getEloquentQuery();
        $studentId = StudentContext::id();

        if ($studentId === null) {
            return $query->whereRaw('0 = 1');
        }

        $model = $query->getModel();

        if ($model instanceof Activity) {
            return $query->whereHas(
                'classSubject.section.students',
                fn (Builder $students) => $students->whereKey($studentId),
            );
        }

        if (in_array('student_id', $model->getFillable(), true)) {
            return $query->where($model->getTable().'.student_id', $studentId);
        }

        return $query->whereRaw('0 = 1');
    }
}
