<?php

namespace App\Models;

use App\Enums\GradingTrack;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = ['grading_track'];

    protected function casts(): array
    {
        return [
            'grading_track' => GradingTrack::class,
        ];
    }

    public static function current(): ?self
    {
        return static::query()->first();
    }
}
