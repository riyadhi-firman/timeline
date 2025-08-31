<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use App\Models\Technician;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ScheduleStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Teknisi', Technician::count())
                ->icon('heroicon-o-users')
                ->color('success')
                ->description('Jumlah teknisi aktif')
                ->descriptionIcon('heroicon-o-user-group'),

            Stat::make('Jadwal Hari Ini', Schedule::whereDate('start_time', Carbon::today())->count())
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->description('Pekerjaan yang dijadwalkan hari ini')
                ->descriptionIcon('heroicon-o-clock'),

            Stat::make('Jadwal Selesai', Schedule::where('status', 'completed')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Total pekerjaan yang telah selesai')
                ->descriptionIcon('heroicon-o-check'),
        ];
    }
}
