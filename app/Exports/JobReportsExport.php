<?php

namespace App\Exports;

use App\Models\JobReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JobReportsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return JobReport::with(['schedule.technicians.user', 'schedule.technicians.division'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jadwal ID',
            'Teknisi',
            'Divisi',
            'Judul Jadwal',
            'Nama Pelanggan',
            'Lokasi',
            'Waktu Mulai',
            'Waktu Selesai',
            'Deskripsi Pekerjaan',
            'Catatan',
            'Status',
            'Dibuat Pada',
        ];
    }

    public function map($report): array
    {
        // Gabungkan nama semua teknisi yang ditugaskan ke jadwal ini
        $technicianNames = $report->schedule->technicians->map(function ($technician) {
            return $technician->user->name;
        })->join(', ');

        // Gabungkan nama divisi dari semua teknisi
        $divisionNames = $report->schedule->technicians->map(function ($technician) {
            return $technician->division->name;
        })->unique()->join(', ');

        return [
            $report->id,
            $report->schedule->id,
            $technicianNames,
            $divisionNames,
            $report->schedule->title,
            $report->schedule->customer_name,
            $report->schedule->location,
            $report->schedule->start_time->format('d/m/Y H:i'),
            $report->schedule->end_time->format('d/m/Y H:i'),
            $report->description,
            $report->notes,
            $report->status,
            $report->created_at->format('d/m/Y H:i'),
        ];
    }
}