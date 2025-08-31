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
            'ID Laporan',
            'ID Jadwal',
            'Teknisi',
            'Divisi',
            'Judul Jadwal',
            'Nama Pelanggan',
            'Lokasi',
            'Waktu Mulai',
            'Waktu Selesai',
            'Isi Laporan',
            'Material yang Digunakan',
            'Status Penyelesaian',
            'Catatan Penyelesaian',
            'Waktu Laporan',
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

        // Format material yang digunakan
        $materialsUsed = '-';
        if ($report->materials_used && is_array($report->materials_used)) {
            $materials = collect($report->materials_used)->map(function ($item, $key) {
                if (is_array($item) && isset($item['name'])) {
                    return "{$item['name']} ({$item['quantity']} {$item['unit']})";
                }
                return null;
            })->filter()->join(', ');
            $materialsUsed = $materials ?: '-';
        }

        // Format status penyelesaian
        $completionStatus = match ($report->completion_status) {
            'completed' => 'Selesai',
            'partial' => 'Sebagian Selesai',
            'pending' => 'Tertunda',
            'cancelled' => 'Dibatalkan',
            default => $report->completion_status,
        };

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
            $report->report_content,
            $materialsUsed,
            $completionStatus,
            $report->completion_notes ?? '-',
            $report->reported_at->format('d/m/Y H:i'),
            $report->created_at->format('d/m/Y H:i'),
        ];
    }
}