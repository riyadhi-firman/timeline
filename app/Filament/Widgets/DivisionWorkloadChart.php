<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DivisionWorkloadChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Pekerjaan per Divisi';

    protected function getOptions(): array
    {
        return [
            'aspectRatio' => 4.5,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $data = Schedule::whereBetween('start_time', [Carbon::now()->subDays(30), Carbon::now()])
            ->join('schedule_technician', 'schedules.id', '=', 'schedule_technician.schedule_id')
            ->join('technicians', 'schedule_technician.technician_id', '=', 'technicians.id')
            ->join('divisions', 'technicians.division_id', '=', 'divisions.id')
            ->select('divisions.name', DB::raw('count(*) as total'))
            ->groupBy('divisions.name')
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
