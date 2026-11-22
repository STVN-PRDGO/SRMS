<?php

namespace App\Filament\Teacher\Resources\AttendanceResource\Pages;

use App\Filament\Teacher\Resources\AttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
