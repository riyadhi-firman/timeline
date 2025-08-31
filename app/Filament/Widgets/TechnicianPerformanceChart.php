<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TechnicianPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Performa Teknisi';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '10s';

    protected function getOptions(): array
    {
        return [
            'aspectRatio' => 4.5,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom'
                ],
                'tooltip' => [
                    'enabled' => true,
                    'intersect' => false,
                    'mode' => 'index'
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false
                    ]
                ],
                'x' => [
                    'grid' => [
                        'display' => false
                    ]
                ]
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3
                ]
            ]
        ];
    }

    protected function getData(): array
    {
        $data = Schedule::where('status', 'completed')
            ->whereBetween('end_time', [Carbon::now()->subDays(30), Carbon::now()])
            ->join('schedule_technician', 'schedules.id', '=', 'schedule_technician.schedule_id')
            ->join('technicians', 'schedule_technician.technician_id', '=', 'technicians.id')
            ->join('users', 'technicians.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pekerjaan Selesai (30 hari terakhir)',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => array_map(function ($color) {
                        return str_replace('0.6', '0.8', $color);
                    }, [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                    ]),
                    'borderColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 99, 132)',
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
        return 'bar';
    }
}
