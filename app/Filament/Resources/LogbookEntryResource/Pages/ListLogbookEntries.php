<?php

namespace App\Filament\Resources\LogbookEntryResource\Pages;

use App\Filament\Resources\LogbookEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogbookEntries extends ListRecords
{
    protected static string $resource = LogbookEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
