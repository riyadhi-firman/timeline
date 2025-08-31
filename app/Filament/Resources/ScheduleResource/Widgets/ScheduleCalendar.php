<?php

namespace App\Filament\Resources\ScheduleResource\Widgets;

use App\Models\Schedule;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ScheduleCalendar extends FullCalendarWidget
{
    /**
     * @var view-string
     */
    protected static string $view = 'filament.resources.schedule-resource.widgets.schedule-calendar';

    public function getViewData(): array
    {
        return [
            'events' => Schedule::all()->map(function (Schedule $schedule) {
                return [
                    'id' => $schedule->id,
                    'title' => $schedule->title,
                    'start' => $schedule->start_time,
                    'end' => $schedule->end_time,
                    'url' => route('filament.admin.resources.schedules.edit', ['record' => $schedule]),
                    'shouldOpenUrlInNewTab' => false,
                    'extendedProps' => [
                        'technician' => $schedule->technician->user->name,
                        'division' => $schedule->technician->division->name,
                        'location' => $schedule->location,
                        'status' => $schedule->status,
                    ],
                ];
            })->toArray(),
        ];
    }
}
