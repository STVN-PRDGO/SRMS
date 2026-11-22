<?php

namespace App\Filament\Admin\Resources\TeacherResource\Pages;

use App\Actions\Users\ProvisionTeacher;
use App\Filament\Admin\Resources\TeacherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTeachers extends ManageRecords
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->using(fn (array $data) => app(ProvisionTeacher::class)($data)),
        ];
    }
}
