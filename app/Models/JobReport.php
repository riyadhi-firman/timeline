<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $schedule_id
 * @property int $technician_id
 * @property string $report_content
 * @property array<array-key, mixed>|null $materials_used
 * @property string|null $before_image
 * @property string|null $after_image
 * @property string|null $customer_signature
 * @property string $completion_status
 * @property string|null $completion_notes
 * @property \Illuminate\Support\Carbon $reported_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Schedule $schedule
 * @property-read \App\Models\Technician $technician
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereAfterImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereBeforeImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereCompletionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereCompletionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereCustomerSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereMaterialsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereReportContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereReportedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereTechnicianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobReport whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class JobReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'technician_id',
        'report_content',
        'materials_used',
        'before_image',
        'after_image',
        'customer_signature',
        'completion_status',
        'completion_notes',
        'reported_at',
    ];

    protected $casts = [
        'materials_used' => 'array',
        'reported_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['report_content', 'completion_notes'];
    }

    public function getGlobalSearchResultTitle(): string
    {
        return "Laporan #{$this->id} - {$this->schedule->title}";
    }

    public function getGlobalSearchResultDetails(): array
    {
        $technicianNames = $this->schedule->technicians->map(function ($technician) {
            return $technician->user->name;
        })->join(', ');

        return [
            'Jadwal' => $this->schedule->title,
            'Teknisi' => $technicianNames,
            'Status' => match ($this->completion_status) {
                'completed' => 'Selesai',
                'partial' => 'Sebagian Selesai',
                'pending' => 'Tertunda',
                'cancelled' => 'Dibatalkan',
                default => $this->completion_status,
            },
            'Tanggal' => $this->reported_at->format('d M Y'),
        ];
    }
}
