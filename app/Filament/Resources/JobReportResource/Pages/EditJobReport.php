<?php

namespace App\Filament\Resources\JobReportResource\Pages;

use App\Filament\Resources\JobReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobReport extends EditRecord
{
    protected static string $resource = JobReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
