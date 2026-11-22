<?php

namespace App\Models;

use App\Actions\Activities\GuardActivity;
use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Activity $activity): void {
            app(GuardActivity::class)($activity);
        });
    }

    protected $fillable = [
        'class_subject_id', 'grading_period_id', 'grading_component_id',
        'title', 'type', 'description', 'max_score', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'max_score' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function gradingPeriod(): BelongsTo
    {
        return $this->belongsTo(GradingPeriod::class);
    }

    public function gradingComponent(): BelongsTo
    {
        return $this->belongsTo(GradingComponent::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }
}
