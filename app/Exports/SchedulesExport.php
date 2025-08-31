<?php

namespace App\Exports;

use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SchedulesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $query = Schedule::with(['technicians.user', 'technicians.division']);
        
        // Jika user bukan super admin, filter berdasarkan divisi
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                $query->whereHas('technicians.division', function ($query) use ($userDivisionId) {
                    $query->where('id', $userDivisionId);
                });
            }
        }
        
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Teknisi',
            'Divisi',
            'Judul',
            'Nama Pelanggan',
            'Telepon Pelanggan',
            'Lokasi',
            'Waktu Mulai',
            'Waktu Selesai',
            'Status',
            'Perangkat/Tool',
            'Catatan',
        ];
    }

    public function map($schedule): array
    {
        // Gabungkan nama semua teknisi yang ditugaskan ke jadwal ini
        $technicianNames = $schedule->technicians->map(function ($technician) {
            return $technician->user->name;
        })->join(', ');

        // Gabungkan nama divisi dari semua teknisi
        $divisionNames = $schedule->technicians->map(function ($technician) {
            return $technician->division->name;
        })->unique()->join(', ');

        return [
            $schedule->id,
            $technicianNames,
            $divisionNames,
            $schedule->title,
            $schedule->customer_name,
            $schedule->customer_phone,
            $schedule->location,
            $schedule->start_time->format('d/m/Y H:i'),
            $schedule->end_time->format('d/m/Y H:i'),
            $schedule->status,
            $schedule->equipment_needed,
            $schedule->notes,
        ];
    }
}
