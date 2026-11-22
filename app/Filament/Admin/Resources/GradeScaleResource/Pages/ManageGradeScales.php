<?php

namespace App\Filament\Admin\Resources\GradeScaleResource\Pages;

use App\Filament\Admin\Resources\GradeScaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGradeScales extends ManageRecords
{
    protected static string $resource = GradeScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
