<?php

namespace App\Models;

use App\Enums\Trend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'grading_period_id', 'general_average', 'attendance_rate',
        'activity_completion_rate', 'performance_level_id', 'trend', 'teacher_remarks', 'computed_at',
    ];

    public function scopeForPeriod(Builder $query, int|GradingPeriod $period): Builder
    {
        $id = $period instanceof GradingPeriod ? $period->id : $period;

        return $query->where('grading_period_id', $id);
    }

    protected function casts(): array
    {
        return [
            'general_average' => 'decimal:2',
            'attendance_rate' => 'decimal:2',
            'activity_completion_rate' => 'decimal:2',
            'trend' => Trend::class,
            'computed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradingPeriod(): BelongsTo
    {
        return $this->belongsTo(GradingPeriod::class);
    }

    public function performanceLevel(): BelongsTo
    {
        return $this->belongsTo(PerformanceLevel::class);
    }
}
