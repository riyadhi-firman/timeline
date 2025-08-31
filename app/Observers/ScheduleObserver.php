<?php

namespace App\Observers;

use App\Models\Schedule;
use App\Notifications\ScheduleAssigned;

class ScheduleObserver
{
    public function created(Schedule $schedule): void
    {
        $technician = $schedule->technician;
        
        if ($technician && $technician->user) {
            $technician->user->notify(new ScheduleAssigned($schedule));

            // Juga notifikasi supervisor jika ada
            if ($technician->division && $technician->division->supervisor && $technician->division->supervisor->user) {
                $technician->division->supervisor->user->notify(new ScheduleAssigned($schedule));
            }
        }
    }
}
