<?php

namespace App\Filament\Teacher\Resources\ActivityResource\Pages;

use App\Filament\Teacher\Resources\ActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageActivities extends ManageRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
