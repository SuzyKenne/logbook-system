<?php

namespace App\Filament\Resources\LogbookResource\Pages;

use App\Filament\Resources\LogbookResource;
use App\Services\LogbookReportService; // Add this import
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;

class ViewLogbooks extends ViewRecord
{
    protected static string $resource = LogbookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addEntry')
                ->label('Add Entry')
                ->icon('heroicon-m-plus')
                ->color('success')
                // ->url(fn() => route('filament.admin.resources.logbook-entries.create', [
                //     'logbook_id' => $this->record->id
                // ]))
                ->visible(fn() => $this->record->status === 'active'),

            // Step 1: Add the report generation action to header actions
            Actions\Action::make('generateReport')
                ->label('Generate Progress Report')
                ->icon('heroicon-m-document-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Generate Progress Report')
                ->modalDescription('This will create a detailed PDF report of all entries and progress for this logbook.')
                ->modalSubmitActionLabel('Generate Report')
                ->action(function ($record) {
                    return redirect()->to(route('logbook.progress-report', $record));
                })
                ->openUrlInNewTab(),


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
                Infolists\Components\Section::make('Logbook Overview')
                    ->description('Basic logbook information and current status')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label('Logbook Title')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->size('lg')
                                    ->color(fn(string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'active' => 'success',
                                        'completed' => 'info',
                                        'cancelled' => 'danger',
                                        'on_hold' => 'warning',
                                    })
                                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucwords($state)))
                                    ->icon(fn(string $state): string => match ($state) {
                                        'draft' => 'heroicon-m-pencil-square',
                                        'active' => 'heroicon-m-play',
                                        'completed' => 'heroicon-m-check-circle',
                                        'cancelled' => 'heroicon-m-x-circle',
                                        'on_hold' => 'heroicon-m-pause',
                                    }),

                                Infolists\Components\TextEntry::make('progress_percentage')
                                    ->label('Completion Progress')
                                    ->state(function ($record): string {
                                        $entriesCount = $record->entries()->count();
                                        $totalSessions = $record->total_sessions;
                                        $percentage = $totalSessions > 0 ? round(($entriesCount / $totalSessions) * 100) : 0;
                                        return $percentage . '%';
                                    })
                                    ->badge()
                                    ->size('lg')
                                    ->color(function ($record): string {
                                        $entriesCount = $record->entries()->count();
                                        $totalSessions = $record->total_sessions;
                                        $percentage = $totalSessions > 0 ? round(($entriesCount / $totalSessions) * 100) : 0;

                                        return match (true) {
                                            $percentage >= 90 => 'success',
                                            $percentage >= 70 => 'warning',
                                            $percentage >= 50 => 'info',
                                            default => 'danger',
                                        };
                                    })
                                    ->icon('heroicon-m-chart-bar'),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull()
                            ->placeholder('No description provided'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Course & Academic Information')
                    ->description('Associated course and academic context')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('course_code')
                                    ->label('Course Code')
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->icon('heroicon-m-academic-cap'),

                                Infolists\Components\TextEntry::make('logbook_type')
                                    ->label('Logbook Type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'laboratory' => 'success',
                                        'field_work' => 'warning',
                                        'practical' => 'info',
                                        'workshop' => 'primary',
                                        'project' => 'danger',
                                        'internship' => 'secondary',
                                        'research' => 'gray',
                                        'seminar' => 'indigo',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucwords($state)))
                                    ->icon(fn(string $state): string => match ($state) {
                                        'laboratory' => 'heroicon-m-beaker',
                                        'field_work' => 'heroicon-m-map',
                                        'practical' => 'heroicon-m-wrench-screwdriver',
                                        'workshop' => 'heroicon-m-cog-6-tooth',
                                        'project' => 'heroicon-m-folder',
                                        'internship' => 'heroicon-m-briefcase',
                                        'research' => 'heroicon-m-magnifying-glass',
                                        'seminar' => 'heroicon-m-presentation-chart-line',
                                        default => 'heroicon-m-document',
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('course_name')
                            ->label('Course Name')
                            ->size('lg')
                            ->weight('medium')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('department.name')
                                    ->label('Department')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-building-office-2'),

                                Infolists\Components\TextEntry::make('level.name')
                                    ->label('Academic Level')
                                    ->badge()
                                    ->color('secondary')
                                    ->icon('heroicon-m-academic-cap'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Schedule & Session Planning')
                    ->description('Timeline and session management details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('start_date')
                                    ->label('Start Date')
                                    ->date('F d, Y')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-calendar'),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('End Date')
                                    ->date('F d, Y')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-calendar'),

                                Infolists\Components\TextEntry::make('duration')
                                    ->label('Duration')
                                    ->state(function ($record): string {
                                        $start = $record->start_date;
                                        $end = $record->end_date;

                                        if (!$start || !$end) {
                                            return 'Not set';
                                        }

                                        $diffInDays = $start->diffInDays($end);
                                        $diffInWeeks = round($diffInDays / 7, 1);

                                        return $diffInDays . ' days (' . $diffInWeeks . ' weeks)';
                                    })
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-clock'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_sessions')
                                    ->label('Total Sessions Planned')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(fn($state): string => $state . ' session' . ($state != 1 ? 's' : ''))
                                    ->icon('heroicon-m-clipboard-document-list'),

                                Infolists\Components\TextEntry::make('completed_sessions')
                                    ->label('Sessions Completed')
                                    ->state(fn($record) => $record->entries()->count())
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn($state): string => $state . ' session' . ($state != 1 ? 's' : ''))
                                    ->icon('heroicon-m-check-circle'),

                                Infolists\Components\TextEntry::make('remaining_sessions')
                                    ->label('Sessions Remaining')
                                    ->state(function ($record): int {
                                        $completed = $record->entries()->count();
                                        $total = $record->total_sessions;
                                        return max(0, $total - $completed);
                                    })
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn($state): string => $state . ' session' . ($state != 1 ? 's' : ''))
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Infolists\Components\Section::make('Entry Statistics & Analytics')
                    ->description('Detailed analysis of logbook entries and activity')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_entries')
                                    ->label('Total Entries')
                                    ->state(fn($record) => $record->entries()->count())
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-document-text'),

                                Infolists\Components\TextEntry::make('recent_entries')
                                    ->label('Entries This Week')
                                    ->state(fn($record) => $record->entries()->where('created_at', '>=', now()->startOfWeek())->count())
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-calendar-days'),

                                Infolists\Components\TextEntry::make('avg_entries_per_week')
                                    ->label('Average Per Week')
                                    ->state(function ($record): string {
                                        $totalEntries = $record->entries()->count();
                                        $startDate = $record->start_date;

                                        if (!$startDate || $totalEntries === 0) {
                                            return '0';
                                        }

                                        $weeksElapsed = max(1, $startDate->diffInWeeks(now()));
                                        $average = round($totalEntries / $weeksElapsed, 1);

                                        return $average . ' entries';
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-chart-bar'),

                                Infolists\Components\TextEntry::make('last_entry_date')
                                    ->label('Last Entry')
                                    ->state(function ($record): string {
                                        $lastEntry = $record->entries()->latest()->first();
                                        return $lastEntry ? $lastEntry->created_at->diffForHumans() : 'No entries yet';
                                    })
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-clock'),
                            ]),

                        Infolists\Components\Fieldset::make('Progress Breakdown')
                            ->schema([
                                Infolists\Components\TextEntry::make('progress_status')
                                    ->label('Progress Analysis')
                                    ->state(function ($record): string {
                                        $completed = $record->entries()->count();
                                        $total = $record->total_sessions;
                                        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

                                        $status = match (true) {
                                            $percentage === 100 => '🎉 Fully completed! All sessions have been logged.',
                                            $percentage >= 90 => '🔥 Almost there! Just a few more sessions to go.',
                                            $percentage >= 75 => '💪 Great progress! You\'re in the final stretch.',
                                            $percentage >= 50 => '📈 Good momentum! Keep up the consistent work.',
                                            $percentage >= 25 => '🚀 Making progress! Stay focused on your goals.',
                                            $percentage > 0 => '🌱 Getting started! Every entry counts.',
                                            default => '📝 Ready to begin! Add your first entry.',
                                        };

                                        return $status;
                                    })
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Administrative Details')
                    ->description('Creation and management information')
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
                                    ->dateTime('F d, Y \a\t g:i A')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-calendar-days'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F d, Y \a\t g:i A')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-pencil-square'),

                                Infolists\Components\TextEntry::make('days_since_creation')
                                    ->label('Age')
                                    ->state(fn($record) => $record->created_at->diffInDays(now()) . ' days old')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                // Step 2: Remove the Quick Actions section that was causing the error
                // The actions are now properly placed in the header actions above
            ]);
    }

    public function getTitle(): string
    {
        return $this->record->title;
    }

    public function getSubheading(): ?string
    {
        return $this->record->course_code . ' - ' . $this->record->course_name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add custom widgets here for charts/graphs
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // You can add custom widgets here for additional information
        ];
    }
}
