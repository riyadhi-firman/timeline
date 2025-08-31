<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan technicians untuk diproses setelah schedule diupdate
        $technicians = $data['technicians'] ?? [];
        unset($data['technicians']);

        // Simpan data technicians di session untuk digunakan setelah update
        session(['schedule_technicians_update' => $technicians]);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var \App\Models\Schedule $record */
        $record = $this->record;
        $technicians = session('schedule_technicians_update', []);

        if (! empty($technicians)) {
            $record->technicians()->sync($technicians);
        }

        session()->forget('schedule_technicians_update');
    }
}
