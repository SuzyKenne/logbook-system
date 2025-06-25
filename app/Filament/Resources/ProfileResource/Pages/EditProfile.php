<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->color('info'),
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Profile Updated')
            ->body('The staff profile has been updated successfully.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
