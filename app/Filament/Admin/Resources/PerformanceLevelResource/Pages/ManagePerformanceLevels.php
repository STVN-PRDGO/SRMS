<?php

namespace App\Filament\Admin\Resources\PerformanceLevelResource\Pages;

use App\Filament\Admin\Resources\PerformanceLevelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePerformanceLevels extends ManageRecords
{
    protected static string $resource = PerformanceLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
