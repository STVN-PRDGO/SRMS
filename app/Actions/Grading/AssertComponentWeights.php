<?php

namespace App\Actions\Grading;

use App\Models\GradingComponent;
use Illuminate\Validation\ValidationException;

class AssertComponentWeights
{
    public function __invoke(?int $subjectId, float $weight, ?int $ignoreId = null): void
    {
        $query = GradingComponent::query()->when(
            $subjectId === null,
            fn ($builder) => $builder->whereNull('subject_id'),
            fn ($builder) => $builder->where('subject_id', $subjectId),
        );

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        $total = round((float) $query->sum('weight') + $weight, 2);

        if ($total > 100) {
            throw ValidationException::withMessages([
                'weight' => 'Weights for this subject cannot add up to more than 100.',
            ]);
        }
    }
}
