<?php

namespace App\Filament\Resources\ScheduleRequestResource\Pages;

use App\Filament\Resources\ScheduleRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScheduleRequest extends EditRecord
{
    protected static string $resource = ScheduleRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
