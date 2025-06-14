<?php

namespace App\Filament\Clusters\AccessControl\Resources\AssignRoleResource\Pages;

use App\Filament\Clusters\AccessControl\Resources\AssignRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAssignRoles extends ListRecords
{
    protected static string $resource = AssignRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
