<?php

namespace App\Models;

use App\Actions\Periods\AssertPeriodDoesNotOverlap;
use App\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingPeriod extends Model
{
    use HasFactory;

    protected $fillable = ['school_year_id', 'name', 'starts_on', 'ends_on', 'status'];

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', PeriodStatus::Open);
    }

    protected static function booted(): void
    {
        static::saving(function (GradingPeriod $period): void {
            app(AssertPeriodDoesNotOverlap::class)(
                (int) $period->school_year_id,
                $period->starts_on instanceof \DateTimeInterface ? $period->starts_on->format('Y-m-d') : (string) $period->starts_on,
                $period->ends_on instanceof \DateTimeInterface ? $period->ends_on->format('Y-m-d') : (string) $period->ends_on,
                $period->exists ? $period->id : null,
            );
        });
    }

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'status' => PeriodStatus::class,
        ];
    }

    public function isClosed(): bool
    {
        return $this->status === PeriodStatus::Closed;
    }

    public function acceptsEntries(): bool
    {
        return $this->status === PeriodStatus::Open;
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
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
