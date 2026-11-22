<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_on', 'ends_on', 'is_current'];

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    protected static function booted(): void
    {
        static::saving(function (SchoolYear $year): void {
            if ($year->is_current) {
                static::query()
                    ->when($year->exists, fn ($query) => $query->whereKeyNot($year->id))
                    ->update(['is_current' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function gradingPeriods(): HasMany
    {
        return $this->hasMany(GradingPeriod::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
