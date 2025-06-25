<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Staff Profile Created')
            ->body('The staff profile has been created successfully.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set boolean fields based on staff_type
        if ($data['staff_type'] === 'dean') {
            $data['is_dean'] = true;
            $data['is_head_of_department'] = false;
        } elseif ($data['staff_type'] === 'hod') {
            $data['is_dean'] = false;
            $data['is_head_of_department'] = true;
        } else {
            $data['is_dean'] = false;
            $data['is_head_of_department'] = false;
        }

        return $data;
    }
}
