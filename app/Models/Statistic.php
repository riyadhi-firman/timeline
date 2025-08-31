<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $period
 * @property string $period_type
 * @property int|null $technician_id
 * @property int|null $division_id
 * @property int $completed_jobs
 * @property int $average_completion_time
 * @property array<array-key, mixed>|null $data_json
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division|null $division
 * @property-read \App\Models\Technician|null $technician
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereAverageCompletionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereCompletedJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereDataJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic wherePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereTechnicianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Statistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'period_type', // daily, weekly, monthly
        'technician_id',
        'division_id',
        'completed_jobs',
        'average_completion_time',
        'data_json',
    ];

    protected $casts = [
        'data_json' => 'array',
        'period' => 'date',
    ];

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
