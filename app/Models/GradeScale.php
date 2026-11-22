<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = ['min_grade', 'max_grade', 'letter', 'remark'];

    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget('grade_scales.all'));
        static::deleted(fn () => cache()->forget('grade_scales.all'));
    }

    /**
     * @return Collection<int, GradeScale>
     */
    public static function allCached(): Collection
    {
        $data = cache()->rememberForever('grade_scales.all', fn () => static::query()->orderByDesc('min_grade')->get()->toArray());

        return static::hydrate($data);
    }

    protected function casts(): array
    {
        return [
            'min_grade' => 'decimal:2',
            'max_grade' => 'decimal:2',
        ];
    }
}
