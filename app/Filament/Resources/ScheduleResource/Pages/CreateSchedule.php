<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Models\Technician;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $supervisor = auth()->user()->supervisor;
        $technicians = $data['technicians'] ?? [];
        
        if ($supervisor && !empty($technicians)) {
            $technicians = Technician::whereIn('id', $technicians)
                ->where('division_id', $supervisor->division_id)
                ->pluck('id')
                ->toArray();
        }
        
        $data['created_by'] = auth()->id();
        
        // Simpan technicians untuk diproses setelah schedule dibuat
        session(['schedule_technicians' => $technicians]);
        unset($data['technicians']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Schedule $record */
        $record = $this->record;
        $technicians = session('schedule_technicians', []);

        if (! empty($technicians)) {
            $record->technicians()->attach($technicians);
        }

        session()->forget('schedule_technicians');
    }
}
