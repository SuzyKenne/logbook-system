<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

class ViewProfile extends ViewRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        ImageEntry::make('profile_image')
                            ->label('Profile Photo')
                            ->circular()
                            ->size(150)
                            ->defaultImageUrl(fn($record) => $record->getDefaultProfileImage())
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.first_name')
                                    ->label('First Name'),
                                TextEntry::make('user.last_name')
                                    ->label('Last Name'),
                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable(),
                                TextEntry::make('user.phone')
                                    ->label('Phone')
                                    ->copyable(),
                                TextEntry::make('staff_id')
                                    ->label('Staff ID')
                                    ->badge(),
                                TextEntry::make('hire_date')
                                    ->label('Date of Hire')
                                    ->date(),
                            ]),
                    ]),

                Section::make('Academic Information')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('faculty.name')
                                    ->label('Faculty'),
                                TextEntry::make('department.name')
                                    ->label('Department'),
                                TextEntry::make('staff_type')
                                    ->label('Staff Type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'dean' => 'success',
                                        'hod' => 'warning',
                                        'lecturer' => 'primary',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'warning',
                                        'suspended' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_dean')
                                    ->label('Is Dean')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-badge')
                                    ->falseIcon('heroicon-o-x-mark')
                                    ->trueColor('success')
                                    ->falseColor('gray'),
                                IconEntry::make('is_head_of_department')
                                    ->label('Is Head of Department')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-badge')
                                    ->falseIcon('heroicon-o-x-mark')
                                    ->trueColor('success')
                                    ->falseColor('gray'),
                            ]),
                    ]),

                Section::make('Professional Details')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        TextEntry::make('designation')
                            ->label('Designation'),
                        TextEntry::make('qualification')
                            ->label('Qualifications')
                            ->markdown(),
                        TextEntry::make('specialization')
                            ->label('Areas of Specialization')
                            ->markdown(),
                        TextEntry::make('bio')
                            ->label('Biography')
                            ->markdown(),
                    ]),

                Section::make('Contact & Office Information')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('office_location')
                                    ->label('Office Location'),
                                TextEntry::make('office_phone')
                                    ->label('Office Phone')
                                    ->copyable(),
                            ]),
                    ]),
            ]);
    }
}
