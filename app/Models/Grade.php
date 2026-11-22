<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Grade extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    protected $fillable = [
        'student_id', 'class_subject_id', 'grading_period_id',
        'raw_grade', 'letter', 'remark', 'is_locked', 'computed_at',
    ];

    public function scopeForPeriod(Builder $query, int|GradingPeriod $period): Builder
    {
        $id = $period instanceof GradingPeriod ? $period->id : $period;

        return $query->where('grading_period_id', $id);
    }

    protected function casts(): array
    {
        return [
            'raw_grade' => 'decimal:2',
            'is_locked' => 'boolean',
            'computed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function gradingPeriod(): BelongsTo
    {
        return $this->belongsTo(GradingPeriod::class);
    }
}
