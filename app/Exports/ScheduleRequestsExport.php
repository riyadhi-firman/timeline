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
        return ScheduleRequest::with(['requester', 'approver'])->get();
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