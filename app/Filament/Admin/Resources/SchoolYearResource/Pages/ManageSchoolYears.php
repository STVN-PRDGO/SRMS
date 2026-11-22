<?php

namespace App\Filament\Admin\Resources\SchoolYearResource\Pages;

use App\Filament\Admin\Resources\SchoolYearResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSchoolYears extends ManageRecords
{
    protected static string $resource = SchoolYearResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
