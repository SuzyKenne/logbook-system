<?php

namespace App\Filament\Resources\LogbookEntryResource\Pages;

use App\Filament\Resources\LogbookEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Group;

class ViewLogbookEntry extends ViewRecord
{
    protected static string $resource = LogbookEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->color('warning')
                ->icon('heroicon-o-pencil-square'),
            Actions\DeleteAction::make()
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Session Overview')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        Infolists\Components\TextEntry::make('logbook.title')
                                            ->label('Logbook')
                                            ->icon('heroicon-o-book-open')
                                            ->color('primary')
                                            ->weight(FontWeight::SemiBold)
                                            ->copyable(),

                                        Infolists\Components\TextEntry::make('session_title')
                                            ->label('Session Title')
                                            ->icon('heroicon-o-academic-cap')
                                            ->color('gray')
                                            ->weight(FontWeight::Medium)
                                            ->copyable(),

                                        Infolists\Components\TextEntry::make('creator.name')
                                            ->label('Created By')
                                            ->icon('heroicon-o-user')
                                            ->color('gray')
                                            ->badge(),
                                    ]),

                                    Group::make([
                                        Infolists\Components\TextEntry::make('entry_date')
                                            ->label('Entry Date')
                                            ->icon('heroicon-o-calendar-days')
                                            ->date('F j, Y')
                                            ->color('success')
                                            ->weight(FontWeight::Medium),

                                        Infolists\Components\TextEntry::make('session_duration')
                                            ->label('Session Duration')
                                            ->icon('heroicon-o-clock')
                                            ->color('warning')
                                            ->state(function ($record) {
                                                if ($record->start_time && $record->end_time) {
                                                    $start = \Carbon\Carbon::parse($record->start_time);
                                                    $end = \Carbon\Carbon::parse($record->end_time);
                                                    $duration = $end->diff($start);
                                                    return $duration->format('%H:%I hours');
                                                }
                                                return 'Not specified';
                                            }),

                                        Infolists\Components\TextEntry::make('time_range')
                                            ->label('Time Range')
                                            ->icon('heroicon-o-clock')
                                            ->color('gray')
                                            ->state(function ($record) {
                                                if ($record->start_time && $record->end_time) {
                                                    return $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i');
                                                }
                                                return 'Not specified';
                                            }),
                                    ]),
                                ]),
                        ])
                            ->from('lg'),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('primary'),

                Section::make('Status Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'submitted' => 'warning',
                                        'reviewed' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn(string $state): string => match ($state) {
                                        'draft' => 'heroicon-o-pencil',
                                        'submitted' => 'heroicon-o-paper-airplane',
                                        'reviewed' => 'heroicon-o-eye',
                                        'approved' => 'heroicon-o-check-circle',
                                        'rejected' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-question-mark-circle',
                                    }),

                                Infolists\Components\TextEntry::make('revisor.name')
                                    ->label('Reviewed By')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('Not yet reviewed')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('revised_at')
                                    ->label('Reviewed At')
                                    ->icon('heroicon-o-calendar-days')
                                    ->dateTime('M j, Y \a\t g:i A')
                                    ->placeholder('Not yet reviewed')
                                    ->color('gray'),
                            ]),
                    ])
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconColor('success')
                    ->collapsible(),

                Section::make('Learning Content')
                    ->schema([
                        Infolists\Components\TextEntry::make('objectives')
                            ->label('Learning Objectives')
                            ->icon('heroicon-o-light-bulb')
                            ->placeholder('No objectives specified')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('content')
                            ->label('Session Content')
                            ->icon('heroicon-o-document-text')
                            ->placeholder('No content provided')
                            ->prose()
                            ->html()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('activities')
                            ->label('Activities Performed')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->placeholder('No activities specified')
                            ->prose()
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-book-open')
                    ->iconColor('primary'),

                Section::make('Outcomes & Assignments')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Infolists\Components\TextEntry::make('outcomes')
                                    ->label('Learning Outcomes')
                                    ->icon('heroicon-o-trophy')
                                    ->placeholder('No outcomes specified')
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('assignments')
                                    ->label('Assignments & Tasks')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->placeholder('No assignments specified')
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('remarks')
                                    ->label('Additional Remarks')
                                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                    ->placeholder('No remarks provided')
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->icon('heroicon-o-star')
                    ->iconColor('warning')
                    ->collapsible(),

                Section::make('Record Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Created At')
                                        ->icon('heroicon-o-plus-circle')
                                        ->dateTime('M j, Y \a\t g:i A')
                                        ->color('success'),

                                    Infolists\Components\TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->icon('heroicon-o-arrow-path')
                                        ->dateTime('M j, Y \a\t g:i A')
                                        ->color('gray'),
                                ]),

                                Group::make([
                                    Infolists\Components\TextEntry::make('attachments_count')
                                        ->label('Attachments')
                                        ->icon('heroicon-o-paper-clip')
                                        ->state(function ($record) {
                                            $count = $record->attachments()->count();
                                            return $count > 0 ? $count . ' file(s)' : 'No attachments';
                                        })
                                        ->color(function ($record) {
                                            return $record->attachments()->count() > 0 ? 'primary' : 'gray';
                                        })
                                        ->badge(),

                                    Infolists\Components\TextEntry::make('id')
                                        ->label('Entry ID')
                                        ->icon('heroicon-o-hashtag')
                                        ->color('gray')
                                        ->copyable(),
                                ]),
                            ]),
                    ])
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('gray')
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    // protected function getTitle(): string
    // {
    //     return 'Logbook Entry: ' . $this->record->session_title;
    // }

    // protected function getSubheading(): string
    // {
    //     return 'Entry for ' . $this->record->logbook->title . ' on ' . $this->record->entry_date->format('F j, Y');
    // }
}
