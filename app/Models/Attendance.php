<?php

namespace App\Models;

use App\Actions\Attendance\GuardAttendance;
use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Attendance $attendance): void {
            app(GuardAttendance::class)($attendance);
        });
    }

    protected $fillable = ['class_subject_id', 'student_id', 'date', 'status', 'remarks'];

    public function scopeForPeriod(Builder $query, GradingPeriod $period): Builder
    {
        return $query->whereDate('date', '>=', $period->starts_on)
            ->whereDate('date', '<=', $period->ends_on);
    }

    public function scopeAttended(Builder $query): Builder
    {
        return $query->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late]);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceStatus::class,
        ];
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
