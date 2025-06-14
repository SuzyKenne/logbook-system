<?php

namespace App\Filament\Clusters\AccessControl\Resources\PermissionResource\Pages;

use App\Filament\Clusters\AccessControl\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
