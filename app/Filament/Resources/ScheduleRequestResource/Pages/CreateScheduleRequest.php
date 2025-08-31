<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateScheduleRequest extends CreateRecord
{
    protected static string $resource = ScheduleRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        return $data;
    }
}
