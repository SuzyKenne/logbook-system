<?php

namespace App\Filament\Resources\LogbookEntryResource\Pages;

use App\Filament\Resources\LogbookEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLogbookEntry extends CreateRecord
{
    protected static string $resource = LogbookEntryResource::class;
}
