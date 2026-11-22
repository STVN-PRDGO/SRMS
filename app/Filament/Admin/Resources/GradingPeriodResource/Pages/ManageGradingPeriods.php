<?php

namespace App\Filament\Admin\Resources\GradingPeriodResource\Pages;

use App\Filament\Admin\Resources\GradingPeriodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGradingPeriods extends ManageRecords
{
    protected static string $resource = GradingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
