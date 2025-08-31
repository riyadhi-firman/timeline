<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CompletionTimeChart extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Waktu Penyelesaian';

    protected static ?int $contentHeight = 300;

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $lastSixMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $lastSixMonths->push(Carbon::now()->subMonths($i)->format('M Y'));
        }

        $averageTimes = collect();
        foreach ($lastSixMonths as $index => $month) {
            $startOfMonth = Carbon::now()->subMonths(5 - $index)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonths(5 - $index)->endOfMonth();

            $query = Schedule::where('status', 'completed')
                ->whereBetween('end_time', [$startOfMonth, $endOfMonth]);

            // Jika user bukan super admin, filter berdasarkan divisi
            if (!auth()->user()->hasRole('super_admin')) {
                if (auth()->user()->supervisor) {
                    $userDivisionId = auth()->user()->supervisor->division_id;
                    $query->whereHas('technicians.division', function ($query) use ($userDivisionId) {
                        $query->where('id', $userDivisionId);
                    });
                }
            }

            $schedules = $query->get();

            $totalMinutes = 0;
            $count = 0;

            foreach ($schedules as $schedule) {
                if ($schedule->start_time && $schedule->end_time) {
                    $totalMinutes += $schedule->start_time->diffInMinutes($schedule->end_time);
                    $count++;
                }
            }

            $averageTimes->push($count > 0 ? round($totalMinutes / $count / 60, 1) : 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Waktu Penyelesaian (jam)',
                    'data' => $averageTimes->toArray(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $lastSixMonths->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
