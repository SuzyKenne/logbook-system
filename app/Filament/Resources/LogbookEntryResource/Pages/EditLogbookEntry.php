<?php

namespace App\Filament\Resources\LogbookEntryResource\Pages;

use App\Filament\Resources\LogbookEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogbookEntry extends EditRecord
{
    protected static string $resource = LogbookEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
