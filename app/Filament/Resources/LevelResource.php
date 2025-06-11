<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Filament\Resources\LevelResource\RelationManagers;
use App\Models\Level;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the level')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name', function (Builder $query) {
                                        return $query->with('faculty');
                                    })
                                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->faculty->name} - {$record->name}")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\Select::make('faculty_id')
                                            ->relationship('faculty', 'name')
                                            ->required(),
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\TextInput::make('code')
                                            ->required(),
                                        Forms\Components\TextInput::make('head_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('head_email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Level 100, Year 1, Freshman')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('e.g., L100, Y1')
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of the level')
                            ->columnSpanFull(),
                    ]),

                Section::make('Academic Details')
                    ->description('Academic year and semester information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('academic_year')
                                    ->label('Academic Year')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('e.g., 2024-2025, 2024/25')
                                    ->default(function () {
                                        $currentYear = Carbon::now()->year;
                                        $nextYear = $currentYear + 1;
                                        return "{$currentYear}-{$nextYear}";
                                    })
                                    ->columnSpan(1),

                                Forms\Components\Select::make('semester')
                                    ->options([
                                        '1' => 'First Semester',
                                        '2' => 'Second Semester',
                                        'summer' => 'Summer',
                                        'all' => 'All Year',
                                    ])
                                    ->default('1')
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Status & Management')
                    ->description('Level status and administrative information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'completed' => 'Completed',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->default('active')
                                    ->columnSpan(1),

                                Forms\Components\Select::make('created_by')
                                    ->label('Created By')
                                    ->relationship('creator', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(auth()->id())
                                    ->required()
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department.faculty.name')
                    ->label('Faculty')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Level Name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Academic Year')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('semester')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '1' => 'First Semester',
                        '2' => 'Second Semester',

                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'warning',
                        '2' => 'info',
                        'summer' => 'success',
                        'all' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('logbooks_count')
                    ->label('Logbooks')
                    ->counts('logbooks')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'completed' => 'info',
                        'suspended' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name', function (Builder $query) {
                        return $query->with('faculty');
                    })
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->faculty->name} - {$record->name}")
                    ->searchable()
                    ->preload(),

                SelectFilter::make('faculty')
                    ->label('Faculty')
                    ->relationship('department.faculty', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('academic_year')
                    ->options(function () {
                        return Level::distinct()
                            ->pluck('academic_year', 'academic_year')
                            ->toArray();
                    })
                    ->searchable(),

                SelectFilter::make('semester')
                    ->options([
                        '1' => 'First Semester',
                        '2' => 'Second Semester',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'completed' => 'Completed',
                        'suspended' => 'Suspended',
                    ]),

                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('has_logbooks')
                    ->label('Has Logbooks')
                    ->query(fn(Builder $query): Builder => $query->has('logbooks')),

                Filter::make('current_academic_year')
                    ->label('Current Academic Year')
                    ->query(function (Builder $query): Builder {
                        $currentYear = Carbon::now()->year;
                        $nextYear = $currentYear + 1;
                        $academicYear = "{$currentYear}-{$nextYear}";
                        return $query->where('academic_year', $academicYear);
                    }),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-m-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'inactive']);
                            });
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-m-check-badge')
                        ->color('info')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'completed']);
                            });
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('change_department')
                        ->label('Change Department')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('department_id')
                                ->label('New Department')
                                ->relationship('department', 'name', function (Builder $query) {
                                    return $query->with('faculty');
                                })
                                ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->faculty->name} - {$record->name}")
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['department_id' => $data['department_id']]);
                            });
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('update_academic_year')
                        ->label('Update Academic Year')
                        ->icon('heroicon-m-calendar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('academic_year')
                                ->label('New Academic Year')
                                ->required()
                                ->placeholder('e.g., 2024-2025'),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['academic_year' => $data['academic_year']]);
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('department.name')
            ->poll('30s');
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\LogbooksRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
