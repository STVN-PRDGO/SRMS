<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSubject extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'subject_id', 'teacher_id', 'schedule'];

    protected function casts(): array
    {
        return [
            'schedule' => 'array',
        ];
    }

    public function isManageableBy(User $user): bool
    {
        if ($user->can('view_all_classes')) {
            return true;
        }

        return $this->teacher_id !== null && $this->teacher_id === $user->teacher?->id;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('view_all_classes')) {
            return $query;
        }

        return $query->where('teacher_id', $user->teacher?->id ?? 0);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
