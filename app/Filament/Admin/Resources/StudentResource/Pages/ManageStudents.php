<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Actions\Users\ProvisionStudent;
use App\Filament\Admin\Resources\StudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStudents extends ManageRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->using(fn (array $data) => app(ProvisionStudent::class)($data)),
        ];
    }
}
