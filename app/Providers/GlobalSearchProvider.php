<?php

namespace App\Providers;

use App\Models\Schedule;
use App\Models\JobReport;
use App\Models\ScheduleRequest;
use App\Models\Technician;
use App\Models\Division;
use App\Models\User;
use App\Models\Supervisor;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Contracts\GlobalSearchProvider as GlobalSearchProviderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GlobalSearchProvider implements GlobalSearchProviderContract
{
    public function getResults(string $query): GlobalSearchResults
    {
        $results = new GlobalSearchResults();

        // Jika query kosong, return empty results
        if (empty(trim($query))) {
            return $results;
        }

        // Search Schedules
        $this->searchSchedules($query, $results);
        
        // Search Job Reports
        $this->searchJobReports($query, $results);
        
        // Search Schedule Requests
        $this->searchScheduleRequests($query, $results);
        
        // Search Technicians
        $this->searchTechnicians($query, $results);
        
        // Search Divisions
        $this->searchDivisions($query, $results);
        
        // Search Users
        $this->searchUsers($query, $results);
        
        // Search Supervisors
        $this->searchSupervisors($query, $results);

        return $results;
    }

    protected function searchSchedules(string $query, GlobalSearchResults $results): void
    {
        $schedules = Schedule::query()
            ->with(['technicians.user', 'technicians.division'])
            ->where(function (Builder $query) {
                $query->where('title', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            });

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $schedules->whereHas('technicians.division', function ($query) use ($userDivisionId) {
                    $query->where('id', $userDivisionId);
                });
            }
        }

        $schedules->limit(5)->get()->each(function (Schedule $schedule) use ($results) {
            $technicianNames = $schedule->technicians->map(function ($technician) {
                return $technician->user->name;
            })->join(', ');

            $results->add(
                GlobalSearchResult::make()
                    ->title($schedule->title)
                    ->url(route('filament.admin.resources.schedules.edit', $schedule))
                    ->details([
                        'Teknisi' => $technicianNames,
                        'Lokasi' => $schedule->location,
                        'Tanggal' => $schedule->start_time->format('d M Y'),
                    ])
                    ->category('Jadwal Pekerjaan')
            );
        });
    }

    protected function searchJobReports(string $query, GlobalSearchResults $results): void
    {
        $jobReports = JobReport::query()
            ->with(['schedule.technicians.user', 'schedule.technicians.division'])
            ->where(function (Builder $query) {
                $query->where('report_content', 'like', "%{$query}%")
                    ->orWhere('completion_notes', 'like', "%{$query}%");
            });

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $jobReports->whereHas('schedule.technicians.division', function ($query) use ($userDivisionId) {
                    $query->where('id', $userDivisionId);
                });
            }
        }

        $jobReports->limit(5)->get()->each(function (JobReport $jobReport) use ($results) {
            $technicianNames = $jobReport->schedule->technicians->map(function ($technician) {
                return $technician->user->name;
            })->join(', ');

            $results->add(
                GlobalSearchResult::make()
                    ->title("Laporan #{$jobReport->id} - {$jobReport->schedule->title}")
                    ->url(route('filament.admin.resources.job-reports.edit', $jobReport))
                    ->details([
                        'Jadwal' => $jobReport->schedule->title,
                        'Teknisi' => $technicianNames,
                        'Status' => match ($jobReport->completion_status) {
                            'completed' => 'Selesai',
                            'partial' => 'Sebagian Selesai',
                            'pending' => 'Tertunda',
                            'cancelled' => 'Dibatalkan',
                            default => $jobReport->completion_status,
                        },
                        'Tanggal' => $jobReport->reported_at->format('d M Y'),
                    ])
                    ->category('Laporan Pekerjaan')
            );
        });
    }

    protected function searchScheduleRequests(string $query, GlobalSearchResults $results): void
    {
        $scheduleRequests = ScheduleRequest::query()
            ->with(['requester.supervisor'])
            ->where(function (Builder $query) {
                $query->where('title', 'like', "%{$query}%")
                    ->orWhere('customer_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            });

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $scheduleRequests->whereHas('requester.supervisor', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }

        $scheduleRequests->limit(5)->get()->each(function (ScheduleRequest $scheduleRequest) use ($results) {
            $results->add(
                GlobalSearchResult::make()
                    ->title("Request #{$scheduleRequest->id} - {$scheduleRequest->title}")
                    ->url(route('filament.admin.resources.schedule-requests.edit', $scheduleRequest))
                    ->details([
                        'Pelanggan' => $scheduleRequest->customer_name,
                        'Lokasi' => $scheduleRequest->location,
                        'Status' => match ($scheduleRequest->status) {
                            'pending' => 'Pending',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => $scheduleRequest->status,
                        },
                        'Diminta Oleh' => $scheduleRequest->requester->name,
                        'Tanggal' => $scheduleRequest->created_at->format('d M Y'),
                    ])
                    ->category('Permintaan Jadwal')
            );
        });
    }

    protected function searchTechnicians(string $query, GlobalSearchResults $results): void
    {
        $technicians = Technician::query()
            ->with(['user', 'division'])
            ->whereHas('user', function (Builder $query) {
                $query->where('name', 'like', "%{$query}%");
            })
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('skill_level', 'like', "%{$query}%");

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $technicians->where('division_id', $userDivisionId);
            }
        }

        $technicians->limit(5)->get()->each(function (Technician $technician) use ($results) {
            $results->add(
                GlobalSearchResult::make()
                    ->title("Teknisi - {$technician->user->name}")
                    ->url(route('filament.admin.resources.technicians.edit', $technician))
                    ->details([
                        'Divisi' => $technician->division->name,
                        'Telepon' => $technician->phone ?? '-',
                        'Skill Level' => $technician->skill_level ?? '-',
                        'Total Jadwal' => $technician->schedules()->count(),
                    ])
                    ->category('Manajemen Divisi')
            );
        });
    }

    protected function searchDivisions(string $query, GlobalSearchResults $results): void
    {
        $divisions = Division::query()
            ->where(function (Builder $query) {
                $query->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $divisions->where('id', $userDivisionId);
            }
        }

        $divisions->limit(5)->get()->each(function (Division $division) use ($results) {
            $results->add(
                GlobalSearchResult::make()
                    ->title("Divisi - {$division->name}")
                    ->url(route('filament.admin.resources.divisions.edit', $division))
                    ->details([
                        'Kode' => $division->code,
                        'Deskripsi' => $division->description ?? '-',
                        'Total Teknisi' => $division->technicians()->count(),
                        'Total Supervisor' => $division->supervisors()->count(),
                    ])
                    ->category('Manajemen Divisi')
            );
        });
    }

    protected function searchUsers(string $query, GlobalSearchResults $results): void
    {
        $users = User::query()
            ->with(['supervisor.division', 'roles'])
            ->where(function (Builder $query) {
                $query->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $users->whereHas('supervisor', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }

        $users->limit(5)->get()->each(function (User $user) use ($results) {
            $roles = $user->roles->pluck('name')->join(', ');
            $division = $user->supervisor ? $user->supervisor->division->name : '-';

            $results->add(
                GlobalSearchResult::make()
                    ->title("User - {$user->name}")
                    ->url(route('filament.admin.resources.users.edit', $user))
                    ->details([
                        'Email' => $user->email,
                        'Role' => $roles ?: '-',
                        'Divisi' => $division,
                        'Bergabung' => $user->created_at->format('d M Y'),
                    ])
                    ->category('Administration')
            );
        });
    }

    protected function searchSupervisors(string $query, GlobalSearchResults $results): void
    {
        $supervisors = Supervisor::query()
            ->with(['user', 'division'])
            ->whereHas('user', function (Builder $query) {
                $query->where('name', 'like', "%{$query}%");
            })
            ->orWhere('phone', 'like', "%{$query}%");

        // Apply division-based filtering
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $supervisors->where('division_id', $userDivisionId);
            }
        }

        $supervisors->limit(5)->get()->each(function (Supervisor $supervisor) use ($results) {
            $results->add(
                GlobalSearchResult::make()
                    ->title("Supervisor - {$supervisor->user->name}")
                    ->url(route('filament.admin.resources.supervisors.edit', $supervisor))
                    ->details([
                        'Divisi' => $supervisor->division->name,
                        'Telepon' => $supervisor->phone ?? '-',
                        'Email' => $supervisor->user->email,
                        'Bergabung' => $supervisor->created_at->format('d M Y'),
                    ])
                    ->category('Manajemen Divisi')
            );
        });
    }
}
