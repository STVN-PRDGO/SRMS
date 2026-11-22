<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'student_no', 'section_id', 'birthdate',
        'guardian_name', 'guardian_contact', 'status',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StudentStatus::Active);
    }

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'status' => StudentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function performanceSummaries(): HasMany
    {
        return $this->hasMany(PerformanceSummary::class);
    }
}
