<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class Deployment extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logOnly(['status', 'user_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
