<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning'),
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Course Overview')
                    ->description('Basic course information and identification')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('course_code')
                                    ->label('Course Code')
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'active' => 'success',
                                        'inactive' => 'warning',
                                        'completed' => 'info',
                                        'cancelled' => 'danger',
                                    })
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                            ]),

                        Infolists\Components\TextEntry::make('course_name')
                            ->label('Course Name')
                            ->size('lg')
                            ->weight('medium')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Course Description')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Academic Information')
                    ->description('Department, level, and academic classification')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Department')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-building-office-2'),

                                Infolists\Components\TextEntry::make('level.name')
                                    ->label('Level')
                                    ->badge()
                                    ->color('secondary')
                                    ->icon('heroicon-m-academic-cap'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('course_type')
                                    ->label('Course Type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'core' => 'danger',
                                        'elective' => 'warning',
                                        'general' => 'info',
                                        'major' => 'success',
                                        'minor' => 'secondary',
                                        'prerequisite' => 'primary',
                                    })
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                                    ->icon('heroicon-m-tag'),

                                Infolists\Components\TextEntry::make('semester')
                                    ->label('Semester')
                                    ->badge()
                                    ->color('gray')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'first' => 'First Semester',
                                        'second' => 'Second Semester',
                                        'summer' => 'Summer Session',
                                        default => ucfirst($state),
                                    })
                                    ->icon('heroicon-m-calendar'),
                            ]),

                        Infolists\Components\TextEntry::make('academic_year')
                            ->label('Academic Year')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($state): string => $state . '/' . ($state + 1))
                            ->icon('heroicon-m-calendar-days'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Course Load & Requirements')
                    ->description('Credit hours, contact hours, and workload information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('credit_hours')
                                    ->label('Credit Hours')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn($state): string => $state . ' Credit' . ($state > 1 ? 's' : ''))
                                    ->icon('heroicon-m-star'),

                                Infolists\Components\TextEntry::make('contact_hours')
                                    ->label('Contact Hours')
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn($state): string => $state . ' Hour' . ($state > 1 ? 's' : '') . '/Week')
                                    ->icon('heroicon-m-clock'),

                                Infolists\Components\TextEntry::make('total_workload')
                                    ->label('Total Weekly Workload')
                                    ->badge()
                                    ->color('info')
                                    ->state(function ($record) {
                                        // Assuming 1 credit hour = 3 total hours of work per week
                                        $totalHours = ($record->credit_hours * 3);
                                        return $totalHours . ' Hours/Week';
                                    })
                                    ->icon('heroicon-m-chart-bar'),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Infolists\Components\Section::make('Statistics & Analytics')
                    ->description('Course enrollment and performance metrics')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_logbooks')
                                    ->label('Total Logbooks')
                                    ->badge()
                                    ->color('primary')
                                    ->state(fn($record) => $record->logbooks()->count())
                                    ->icon('heroicon-m-book-open'),

                                Infolists\Components\TextEntry::make('active_logbooks')
                                    ->label('Active Logbooks')
                                    ->badge()
                                    ->color('success')
                                    ->state(fn($record) => $record->logbooks()->where('status', 'active')->count())
                                    ->icon('heroicon-m-check-circle'),

                                Infolists\Components\TextEntry::make('completed_logbooks')
                                    ->label('Completed Logbooks')
                                    ->badge()
                                    ->color('info')
                                    ->state(fn($record) => $record->logbooks()->where('status', 'completed')->count())
                                    ->icon('heroicon-m-archive-box'),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Administrative Information')
                    ->description('Creation details and administrative metadata')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('creator.name')
                                    ->label('Created By')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-user'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created On')
                                    ->dateTime('M d, Y \a\t g:i A')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-calendar-days'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M d, Y \a\t g:i A')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-pencil-square'),

                                Infolists\Components\TextEntry::make('last_activity')
                                    ->label('Last Activity')
                                    ->state(function ($record) {
                                        $lastLogbook = $record->logbooks()->latest()->first();
                                        return $lastLogbook ?
                                            $lastLogbook->created_at->diffForHumans() :
                                            'No activity yet';
                                    })
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function getTitle(): string
    {
        return $this->record->course_code . ' - ' . $this->record->course_name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add custom widgets here if needed
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // You can add custom widgets here if needed
        ];
    }
}
