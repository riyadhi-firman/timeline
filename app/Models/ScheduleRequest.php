<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRequest extends Model
{
    protected $fillable = [
        'title',
        'customer_name',
        'customer_phone',
        'description',
        'location',
        'equipment_needed',
        'status',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function toSchedule(): Schedule
    {
        $schedule = Schedule::create([
            'title' => $this->title,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'description' => $this->description,
            'location' => $this->location,
            'equipment_needed' => $this->equipment_needed,
            'status' => 'scheduled', // Set initial status
            'notes' => $this->notes,
            'created_by' => $this->approved_by, // The approver becomes the creator
            'start_time' => now(), // Set default start time to current time
            'end_time' => now()->addHour() // Set default end time to 1 hour after start time
        ]);

        // Update the request status
        $this->update(['status' => 'approved']);

        return $schedule;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'customer_name', 'description', 'location', 'notes'];
    }

    public function getGlobalSearchResultTitle(): string
    {
        return "Request #{$this->id} - {$this->title}";
    }

    public function getGlobalSearchResultDetails(): array
    {
        return [
            'Pelanggan' => $this->customer_name,
            'Lokasi' => $this->location,
            'Status' => match ($this->status) {
                'pending' => 'Pending',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default => $this->status,
            },
            'Diminta Oleh' => $this->requester->name,
            'Tanggal' => $this->created_at->format('d M Y'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        
        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->whereHas('requester.supervisor', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }
        
        return $query;
    }
}