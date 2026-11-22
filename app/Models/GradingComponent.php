<?php

namespace App\Models;

use App\Actions\Grading\AssertComponentWeights;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingComponent extends Model
{
    use HasFactory;

    protected $fillable = ['subject_id', 'name', 'weight'];

    protected static function booted(): void
    {
        static::saving(function (GradingComponent $component): void {
            app(AssertComponentWeights::class)(
                $component->subject_id ? (int) $component->subject_id : null,
                (float) $component->weight,
                $component->exists ? $component->id : null,
            );
        });

        static::saved(fn () => cache()->forget('grading_components.all'));
        static::deleted(fn () => cache()->forget('grading_components.all'));
    }

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
