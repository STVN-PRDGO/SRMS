<?php

namespace App\Filament\Admin\Resources\ClassSubjectResource\Pages;

use App\Filament\Admin\Resources\ClassSubjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageClassSubjects extends ManageRecords
{
    protected static string $resource = ClassSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
