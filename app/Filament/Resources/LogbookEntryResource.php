<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogbookEntryResource\Pages;
use App\Filament\Resources\LogbookEntryResource\RelationManagers;
use App\Models\LogbookEntry;
use App\Models\Logbook;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;

class LogbookEntryResource extends Resource
{
    protected static ?string $model = LogbookEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Logbook Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'session_title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('logbook_id')
                                    ->label('Logbook')
                                    ->relationship('logbook', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('created_by')
                                    ->label('Created By')
                                    ->relationship('creator', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(Auth::id())
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('entry_date')
                                    ->label('Entry Date')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),

                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Start Time')
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\TimePicker::make('end_time')
                                    ->label('End Time')
                                    ->required()
                                    ->after('start_time')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\TextInput::make('session_title')
                            ->label('Session Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Session Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ]),

                        Forms\Components\Textarea::make('objectives')
                            ->label('Learning Objectives')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('activities')
                            ->label('Activities Performed')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('outcomes')
                            ->label('Learning Outcomes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('assignments')
                            ->label('Assignments/Tasks')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('remarks')
                            ->label('Additional Remarks')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status & Review')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Submitted',
                                        'revised' => 'Reviewed',
                                        // 'approved' => 'Approved',
                                        // 'rejected' => 'Rejected',
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('revised_by')
                                    ->label('Revised By')
                                    ->relationship('revisor', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\DateTimePicker::make('revised_at')
                            ->label('Revised At')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('logbook.title')
                    ->label('Logbook')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('session_title')
                    ->label('Session Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->time('H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'submitted',
                        'primary' => 'reviewed',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-pencil' => 'draft',
                        'heroicon-o-paper-airplane' => 'submitted',
                        'heroicon-o-eye' => 'reviewed',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('revisor.name')
                    ->label('Revised By')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not revised'),

                Tables\Columns\TextColumn::make('revised_at')
                    ->label('Revised At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Not revised'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('logbook')
                    ->relationship('logbook', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'reviewed' => 'Reviewed',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),

                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date'),
                        DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('primary'),
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('entry_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // You can add relation managers here if needed
            // For example: AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogbookEntries::route('/'),
            'create' => Pages\CreateLogbookEntry::route('/create'),
            'view' => Pages\ViewLogbookEntry::route('/{record}'),
            'edit' => Pages\EditLogbookEntry::route('/{record}/edit'),
        ];
    }

    // Removed getEloquentQuery method since model doesn't use soft deletes

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}
