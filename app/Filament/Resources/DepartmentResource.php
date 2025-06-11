<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
use App\Models\Department;
use App\Models\Faculty;
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
use Filament\Forms\Get;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the department')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('faculty_id')
                                    ->label('Faculty')
                                    ->relationship('faculty', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                        Forms\Components\TextInput::make('code')
                                            ->required(),
                                        Forms\Components\TextInput::make('dean_name')
                                            ->required(),
                                        Forms\Components\TextInput::make('dean_email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Computer Science')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('e.g., CS')
                                    ->unique(ignoreRecord: true)
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of the department')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('location')
                            ->maxLength(255)
                            ->placeholder('e.g., Science Building, Floor 3')
                            ->columnSpanFull(),
                    ]),

                Section::make('Department Head Information')
                    ->description('Information about the department head')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('head_name')
                                    ->label('Head Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Dr. Jane Doe')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('head_email')
                                    ->label('Head Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('head@university.edu')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Status & Management')
                    ->description('Department status and administrative information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
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
                Tables\Columns\TextColumn::make('faculty.name')
                    ->label('Faculty')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('head_name')
                    ->label('Department Head')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('head_email')
                    ->label('Head Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->icon('heroicon-m-map-pin')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('levels_count')
                    ->label('Levels')
                    ->counts('levels')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('logbooks_count')
                    ->label('Logbooks')
                    ->counts('logbooks')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
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
                SelectFilter::make('faculty_id')
                    ->label('Faculty')
                    ->relationship('faculty', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('has_levels')
                    ->label('Has Levels')
                    ->query(fn(Builder $query): Builder => $query->has('levels')),

                Filter::make('has_logbooks')
                    ->label('Has Logbooks')
                    ->query(fn(Builder $query): Builder => $query->has('logbooks')),

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

                    Tables\Actions\BulkAction::make('change_faculty')
                        ->label('Change Faculty')
                        ->icon('heroicon-m-arrow-path')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('faculty_id')
                                ->label('New Faculty')
                                ->relationship('faculty', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['faculty_id' => $data['faculty_id']]);
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name')
            ->poll('30s');
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\LevelsRelationManager::class,
    //         RelationManagers\LogbooksRelationManager::class,
    //         RelationManagers\UserAssignmentsRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
