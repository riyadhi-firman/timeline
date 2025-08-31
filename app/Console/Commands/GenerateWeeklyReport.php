<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Models\Schedule;
use App\Models\Statistic;
use App\Models\Technician;
use App\Models\User;
use App\Notifications\WeeklyReportNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateWeeklyReport extends Command
{
    protected $signature = 'report:weekly';

    protected $description = 'Generate weekly report statistics';

    public function handle()
    {
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
        $period = $startOfWeek->format('Y-m-d');

        $this->info("Generating weekly report for {$startOfWeek->format('Y-m-d')} to {$endOfWeek->format('Y-m-d')}");

        // Statistik per teknisi
        $technicians = Technician::all();
        foreach ($technicians as $technician) {
            $completedJobs = Schedule::where('status', 'completed')
                ->whereBetween('end_time', [$startOfWeek, $endOfWeek])
                ->whereHas('technicians', function ($query) use ($technician) {
                    $query->where('technician_id', $technician->id);
                })
                ->count();

            $schedules = Schedule::where('status', 'completed')
                ->whereBetween('end_time', [$startOfWeek, $endOfWeek])
                ->whereHas('technicians', function ($query) use ($technician) {
                    $query->where('technician_id', $technician->id);
                })
                ->get();

            $totalMinutes = 0;
            $count = 0;

            foreach ($schedules as $schedule) {
                if ($schedule->start_time && $schedule->end_time) {
                    $totalMinutes += $schedule->start_time->diffInMinutes($schedule->end_time);
                    $count++;
                }
            }

            $averageCompletionTime = $count > 0 ? round($totalMinutes / $count) : 0;

            Statistic::updateOrCreate(
                [
                    'period' => $period,
                    'period_type' => 'weekly',
                    'technician_id' => $technician->id,
                ],
                [
                    'completed_jobs' => $completedJobs,
                    'average_completion_time' => $averageCompletionTime,
                ]
            );
        }

        // Statistik per divisi
        $divisions = Division::all();
        foreach ($divisions as $division) {
            $completedJobs = Schedule::where('status', 'completed')
                ->whereBetween('end_time', [$startOfWeek, $endOfWeek])
                ->whereHas('technicians', function ($query) use ($division) {
                    $query->where('division_id', $division->id);
                })
                ->count();

            Statistic::updateOrCreate(
                [
                    'period' => $period,
                    'period_type' => 'weekly',
                    'division_id' => $division->id,
                ],
                [
                    'completed_jobs' => $completedJobs,
                ]
            );
        }

        // Kirim notifikasi ke admin dan supervisor
        $admins = User::role('super_admin')->get(); // Ubah 'admin' menjadi 'Super Admin'
        foreach ($admins as $admin) {
            $admin->notify(new WeeklyReportNotification($startOfWeek, $endOfWeek));
        }

        $this->info('Weekly report generated successfully');

        return 0;
    }
}
