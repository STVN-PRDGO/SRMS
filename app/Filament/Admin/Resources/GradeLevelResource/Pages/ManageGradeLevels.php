<?php

namespace App\Filament\Admin\Resources\GradeLevelResource\Pages;

use App\Filament\Admin\Resources\GradeLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGradeLevels extends ManageRecords
{
    protected static string $resource = GradeLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
