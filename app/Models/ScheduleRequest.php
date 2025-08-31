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
}