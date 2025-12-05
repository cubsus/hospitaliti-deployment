<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Deployment extends Model
{
    use LogsActivity;

    protected static ?int $activityCauserId = null;

    protected $fillable = [
        'user_id',
        'status',
        'output',
        'error_output',
        'exit_code',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'exit_code' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public static function setActivityCauser(?int $userId): void
    {
        static::$activityCauserId = $userId;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (static::$activityCauserId) {
            $activity->causer_id = static::$activityCauserId;
            $activity->causer_type = User::class;
        }
    }
}
