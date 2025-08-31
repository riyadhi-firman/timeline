<?php

namespace App\Exports;

use App\Models\ScheduleRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScheduleRequestsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $query = ScheduleRequest::with(['requester', 'approver']);
        
        // Jika user bukan super admin, filter berdasarkan divisi
        if (!auth()->user()->hasRole('super_admin')) {
            if (auth()->user()->supervisor) {
                $userDivisionId = auth()->user()->supervisor->division_id;
                // Filter berdasarkan divisi dari user yang membuat request
                $query->whereHas('requester.supervisor', function ($query) use ($userDivisionId) {
                    $query->where('division_id', $userDivisionId);
                });
            }
        }
        
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Judul',
            'Nama Pelanggan',
            'Telepon Pelanggan',
            'Lokasi',
            'Deskripsi',
            'Perangkat/Tool',
            'Status',
            'Catatan',
            'Diminta Oleh',
            'Disetujui Oleh',
            'Waktu Disetujui',
            'Dibuat Pada',
        ];
    }

    public function map($request): array
    {
        return [
            $request->id,
            $request->title,
            $request->customer_name,
            $request->customer_phone,
            $request->location,
            $request->description,
            $request->equipment_needed,
            $request->status,
            $request->notes,
            $request->requester->name,
            $request->approver ? $request->approver->name : '-',
            $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : '-',
            $request->created_at->format('d/m/Y H:i'),
        ];
    }
}