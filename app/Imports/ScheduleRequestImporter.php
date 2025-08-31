<?php

namespace App\Imports;

use App\Models\ScheduleRequest;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ScheduleRequestImporter extends Importer
{
    protected static ?string $model = ScheduleRequest::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->label('Judul')
                ->rules(['required']),
            ImportColumn::make('customer_name')
                ->label('Nama Pelanggan')
                ->rules(['required']),
            ImportColumn::make('customer_phone')
                ->label('Nomor Telepon')
                ->rules(['required']),
            ImportColumn::make('description')
                ->label('Deskripsi')
                ->rules(['required']),
            ImportColumn::make('location')
                ->label('Lokasi')
                ->rules(['required']),
            ImportColumn::make('equipment_needed')
                ->label('Peralatan yang Dibutuhkan'),
            ImportColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    public function resolveRecord(): ?ScheduleRequest
    {
        return new ScheduleRequest([
            'title' => $this->data['title'],
            'customer_name' => $this->data['customer_name'],
            'customer_phone' => $this->data['customer_phone'],
            'description' => $this->data['description'],
            'location' => $this->data['location'],
            'equipment_needed' => $this->data['equipment_needed'] ?? null,
            'notes' => $this->data['notes'] ?? null,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Berhasil mengimpor ' . number_format($import->successful_rows) . ' permintaan jadwal.';
    }

    public function getJobChunkSize(): int
    {
        return 100;
    }
}