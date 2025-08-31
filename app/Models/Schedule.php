<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $technician_id
 * @property string $title
 * @property string|null $customer_name
 * @property string|null $customer_phone
 * @property string|null $description
 * @property string $location
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property string $status
 * @property string|null $notes
 * @property string|null $equipment_needed
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\JobReport> $jobReports
 * @property-read int|null $job_reports_count
 * @property-read \App\Models\Technician $technician
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Technician> $technicians
 * @property-read int|null $technicians_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereEquipmentNeeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereTechnicianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Schedule whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        // Hapus 'technician_id' dari sini
        'title',
        'description',
        'location',
        'start_time',
        'end_time',
        'status',
        'notes',
        'created_by',
        'customer_name',
        'customer_phone',
        'equipment_needed',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Ganti relasi belongsTo dengan belongsToMany
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(Technician::class, 'schedule_technician');
    }

    // Tetap pertahankan method ini untuk kompatibilitas
    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'location', 'notes'];
    }

    public function getGlobalSearchResultTitle(): string
    {
        return $this->title;
    }

    public function getGlobalSearchResultDetails(): array
    {
        $technicianNames = $this->technicians->map(function ($technician) {
            return $technician->user->name;
        })->join(', ');

        return [
            'Teknisi' => $technicianNames,
            'Lokasi' => $this->location,
            'Tanggal' => $this->start_time->format('d M Y'),
        ];
    }

    // Tambahkan method ini ke model Schedule
    public function jobReports()
    {
        return $this->hasMany(JobReport::class);
    }
}
