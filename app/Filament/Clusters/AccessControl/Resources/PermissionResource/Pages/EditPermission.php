<?php

namespace App\Filament\Clusters\AccessControl\Resources\PermissionResource\Pages;

use App\Filament\Clusters\AccessControl\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
