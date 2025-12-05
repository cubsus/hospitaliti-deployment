<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Table: activity_log
*
* === Columns ===
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property string|null $event
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property \Illuminate\Support\Collection|null $properties
 * @property string|null $batch_uuid
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
*
* === Accessors/Attributes ===
 * @property-read mixed $subjectInfo
*/
class Activity extends SpatieActivity
{
    use HasFactory;

    public function causerInfo(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->causer?->name ?? 'System'
        );
    }

    public function subjectInfo(): Attribute
    {
        return Attribute::make(
            get: fn() => class_basename($this->subject_type) . ' #' . $this->subject_id
        );
    }
}
