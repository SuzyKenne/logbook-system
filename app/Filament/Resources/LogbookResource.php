<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogbookResource\Pages;
use App\Filament\Resources\LogbookResource\RelationManagers;
use App\Models\Logbook;
use App\Models\Department;
use App\Models\Level;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;

class LogbookResource extends Resource
{
    protected static ?string $model = Logbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Logbook Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Logbook Information')
                    ->description('Basic logbook identification and classification')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('level_id', null);
                                        $set('course_code', null);
                                        $set('course_name', null);
                                    })
                                    ->columnSpan(1),

                                Forms\Components\Select::make('level_id')
                                    ->label('Level')
                                    ->relationship('level', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('course_code', null);
                                        $set('course_name', null);
                                    })
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\TextInput::make('title')
                            ->label('Logbook Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Data Structures Laboratory Sessions - Fall 2024')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Brief description of the logbook purpose and content')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Course Details')
                    ->description('Associate this logbook with a specific course')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('course_code')
                                    ->label('Course Code')
                                    ->searchable()
                                    ->options(function (Get $get) {
                                        $departmentId = $get('department_id');
                                        $levelId = $get('level_id');

                                        if (!$departmentId || !$levelId) {
                                            return [];
                                        }

                                        return Course::where('department_id', $departmentId)
                                            ->where('level_id', $levelId)
                                            ->where('status', 'active')
                                            ->pluck('course_code', 'course_code');
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if ($state) {
                                            $course = Course::where('course_code', $state)
                                                ->where('department_id', $get('department_id'))
                                                ->where('level_id', $get('level_id'))
                                                ->first();

                                            $set('course_name', $course?->course_name);
                                        } else {
                                            $set('course_name', null);
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('course_name')
                                    ->label('Course Name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Select::make('logbook_type')
                            ->label('Logbook Type')
                            ->options([
                                'laboratory' => 'Laboratory Sessions',
                                'field_work' => 'Field Work',
                                'practical' => 'Practical Sessions',
                                'workshop' => 'Workshop Activities',
                                'project' => 'Project Work',
                                'internship' => 'Internship Log',
                                'research' => 'Research Activities',
                                'seminar' => 'Seminar Sessions',
                            ])
                            ->required()
                            ->default('laboratory')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Schedule & Sessions')
                    ->description('Define the timeline and session requirements')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required()
                                    ->default(now())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        $endDate = $get('end_date');
                                        if ($state && $endDate && $state > $endDate) {
                                            $set('end_date', null);
                                        }
                                    })
                                    ->columnSpan(1),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->minDate(fn(Get $get): ?string => $get('start_date'))
                                    ->live()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('total_sessions')
                                    ->label('Total Sessions')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->default(12)
                                    ->suffix('sessions')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'on_hold' => 'On Hold',
                            ])
                            ->required()
                            ->default('draft')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('creator_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Logbook Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('course_code')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->limit(20),

                Tables\Columns\TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('logbook_type')
                    ->label('Type')
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
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucwords($state))),

                Tables\Columns\TextColumn::make('total_sessions')
                    ->label('Sessions')
                    ->alignCenter()
                    ->sortable()
                    ->suffix(' total'),

                Tables\Columns\TextColumn::make('entries_count')
                    ->label('Entries')
                    ->counts('entries')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Progress')
                    ->state(function (Logbook $record): string {
                        $entriesCount = $record->entries()->count();
                        $totalSessions = $record->total_sessions;
                        $percentage = $totalSessions > 0 ? round(($entriesCount / $totalSessions) * 100) : 0;
                        return $percentage . '%';
                    })
                    ->badge(),
                // ->color(fn (string $state): string => {
                //     $percentage = (int) str_replace('%', '', $state);
                //     return match (true) {
                //         $percentage >= 90 => 'success',
                //         $percentage >= 70 => 'warning',
                //         $percentage >= 50 => 'info',
                //         default => 'danger',
                //     };
                // }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        'on_hold' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => str_replace('_', ' ', ucwords($state))),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('level_id')
                    ->label('Level')
                    ->relationship('level', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('logbook_type')
                    ->label('Logbook Type')
                    ->options([
                        'course' => 'Field Work',
                        'lab' => 'Laboratory Sessions',
                        'project' => 'Project Work',
                        'general' => 'Practical Sessions',
                        // 'workshop' => 'Workshop Activities',

                        // 'internship' => 'Internship Log',
                        // 'research' => 'Research Activities',
                        // 'seminar' => 'Seminar Sessions',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'on_hold' => 'On Hold',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('progress')
                    ->label('Progress Filter')
                    ->form([
                        Forms\Components\Select::make('progress_level')
                            ->label('Progress Level')
                            ->options([
                                'not_started' => 'Not Started (0%)',
                                'low' => 'Low Progress (1-25%)',
                                'medium' => 'Medium Progress (26-75%)',
                                'high' => 'High Progress (76-99%)',
                                'completed' => 'Completed (100%)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['progress_level']) {
                            return $query;
                        }

                        return $query->withCount('entries')->having(
                            \DB::raw('CASE 
                                WHEN total_sessions = 0 THEN 0 
                                ELSE ROUND((entries_count / total_sessions) * 100) 
                            END'),
                            match ($data['progress_level']) {
                                'not_started' => '=',
                                'low' => 'between',
                                'medium' => 'between',
                                'high' => 'between',
                                'completed' => '>=',
                            },
                            match ($data['progress_level']) {
                                'not_started' => 0,
                                'low' => [1, 25],
                                'medium' => [26, 75],
                                'high' => [76, 99],
                                'completed' => 100,
                            }
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('New Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'active' => 'Active',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                    'on_hold' => 'On Hold',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\EntriesRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogbooks::route('/'),
            'create' => Pages\CreateLogbook::route('/create'),
            'view' => Pages\ViewLogbooks::route('/{record}'),
            'edit' => Pages\EditLogbook::route('/{record}/edit'),
        ];
    }
}
