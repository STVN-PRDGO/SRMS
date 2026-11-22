<?php

namespace App\Filament\Admin\Resources\GradingComponentResource\Pages;

use App\Filament\Admin\Resources\GradingComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGradingComponents extends ManageRecords
{
    protected static string $resource = GradingComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
