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
        // Filter berdasarkan divisi jika bukan super admin
        $divisionFilter = function ($query) {
            if (!auth()->user()->hasRole('super_admin')) {
                if (auth()->user()->supervisor) {
                    $userDivisionId = auth()->user()->supervisor->division_id;
                    $query->where('division_id', $userDivisionId);
                }
            }
        };

        $scheduleFilter = function ($query) {
            if (!auth()->user()->hasRole('super_admin')) {
                if (auth()->user()->supervisor) {
                    $userDivisionId = auth()->user()->supervisor->division_id;
                    $query->whereHas('technicians.division', function ($query) use ($userDivisionId) {
                        $query->where('id', $userDivisionId);
                    });
                }
            }
        };

        return [
            Stat::make('Total Teknisi', function () use ($divisionFilter) {
                $query = Technician::query();
                $divisionFilter($query);
                return $query->count();
            })
                ->icon('heroicon-o-users')
                ->color('success')
                ->description('Jumlah teknisi aktif')
                ->descriptionIcon('heroicon-o-user-group'),

            Stat::make('Jadwal Hari Ini', function () use ($scheduleFilter) {
                $query = Schedule::whereDate('start_time', Carbon::today());
                $scheduleFilter($query);
                return $query->count();
            })
                ->icon('heroicon-o-calendar')
                ->color('warning')
                ->description('Pekerjaan yang dijadwalkan hari ini')
                ->descriptionIcon('heroicon-o-clock'),

            Stat::make('Jadwal Selesai', function () use ($scheduleFilter) {
                $query = Schedule::where('status', 'completed');
                $scheduleFilter($query);
                return $query->count();
            })
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description('Total pekerjaan yang telah selesai')
                ->descriptionIcon('heroicon-o-check'),
        ];
    }
}
