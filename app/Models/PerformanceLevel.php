<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'min_grade', 'max_grade', 'min_attendance', 'order'];

    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget('performance_levels.all'));
        static::deleted(fn () => cache()->forget('performance_levels.all'));
    }

    protected function casts(): array
    {
        return [
            'min_grade' => 'decimal:2',
            'max_grade' => 'decimal:2',
            'min_attendance' => 'decimal:2',
        ];
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(PerformanceSummary::class);
    }
}
