<?php

namespace App\Models;

use App\Enums\ScoreStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Score extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    protected $fillable = ['activity_id', 'student_id', 'score', 'status', 'remarks', 'graded_by'];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'status' => ScoreStatus::class,
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
