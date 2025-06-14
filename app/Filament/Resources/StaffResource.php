<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\Staff;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\IconEntry;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User Account')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(User::class),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return User::create($data)->id;
                            }),

                        Forms\Components\TextInput::make('staff_id')
                            ->label('Staff ID')
                            ->required()
                            ->unique(Staff::class, 'staff_id', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('designation')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('qualification')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('specialization')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('hire_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Academic Assignment')
                    ->schema([
                        Forms\Components\Select::make('faculty_id')
                            ->label('Faculty')
                            ->options(Faculty::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('department_id', null)),

                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(function (callable $get) {
                                $facultyId = $get('faculty_id');
                                if (!$facultyId) {
                                    return [];
                                }
                                return Department::where('faculty_id', $facultyId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('staff_type')
                            ->label('Staff Type')
                            ->options([
                                'dean' => 'Dean',
                                'hod' => 'Head of Department',
                                'lecturer' => 'Lecturer',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Toggle::make('is_dean')
                            ->label('Is Dean')
                            ->visible(fn(callable $get) => $get('staff_type') === 'dean'),

                        Forms\Components\Toggle::make('is_head_of_department')
                            ->label('Is Head of Department')
                            ->visible(fn(callable $get) => $get('staff_type') === 'hod'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact & Office Information')
                    ->schema([
                        Forms\Components\TextInput::make('office_location')
                            ->label('Office Location')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('office_phone')
                            ->label('Office Phone')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('bio')
                            ->label('Biography')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff_id')
                    ->label('Staff ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('faculty.name')
                    ->label('Faculty')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('staff_type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'dean',
                        'success' => 'hod',
                        'warning' => 'lecturer',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'dean' => 'Dean',
                            'hod' => 'HOD',
                            'lecturer' => 'Lecturer',
                            default => $state,
                        };
                    }),

                Tables\Columns\TextColumn::make('designation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'suspended',
                        'warning' => 'inactive',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\IconColumn::make('is_dean')
                    ->label('Dean')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_head_of_department')
                    ->label('HOD')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('office_location')
                    ->label('Office')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Hire Date')
                    ->date()
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
                Tables\Filters\SelectFilter::make('faculty_id')
                    ->label('Faculty')
                    ->options(Faculty::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('staff_type')
                    ->label('Staff Type')
                    ->options([
                        'dean' => 'Dean',
                        'hod' => 'Head of Department',
                        'lecturer' => 'Lecturer',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\Filter::make('is_dean')
                    ->query(fn(Builder $query): Builder => $query->where('is_dean', true))
                    ->label('Deans Only'),

                Tables\Filters\Filter::make('is_hod')
                    ->query(fn(Builder $query): Builder => $query->where('is_head_of_department', true))
                    ->label('HODs Only'),

                Tables\Filters\Filter::make('active_staff')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'active'))
                    ->label('Active Staff')
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'active']);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'inactive']);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        TextEntry::make('user.first_name')
                            ->label('First Name'),
                        TextEntry::make('user.last_name')
                            ->label('Last Name'),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('user.phone')
                            ->label('Phone'),
                        TextEntry::make('staff_id')
                            ->label('Staff ID'),
                        TextEntry::make('designation'),
                    ])
                    ->columns(2),

                Section::make('Academic Information')
                    ->schema([
                        TextEntry::make('faculty.name')
                            ->label('Faculty'),
                        TextEntry::make('department.name')
                            ->label('Department'),
                        TextEntry::make('staff_type')
                            ->label('Staff Type')
                            ->badge()
                            ->formatStateUsing(function (string $state): string {
                                return match ($state) {
                                    'dean' => 'Dean',
                                    'hod' => 'Head of Department',
                                    'lecturer' => 'Lecturer',
                                    default => $state,
                                };
                            }),
                        TextEntry::make('qualification'),
                        TextEntry::make('specialization'),
                        TextEntry::make('hire_date')
                            ->label('Hire Date')
                            ->date(),
                    ])
                    ->columns(2),

                Section::make('Office Information')
                    ->schema([
                        TextEntry::make('office_location')
                            ->label('Office Location'),
                        TextEntry::make('office_phone')
                            ->label('Office Phone'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'warning',
                                'suspended' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('bio')
                            ->label('Biography')
                            ->prose()
                            ->columnSpanFull(),
                    ]),

                Section::make('System Information')
                    ->schema([
                        // Use IconEntry for boolean fields instead of TextEntry::boolean()
                        \Filament\Infolists\Components\IconEntry::make('is_dean')
                            ->label('Is Dean')
                            ->boolean(),
                        \Filament\Infolists\Components\IconEntry::make('is_head_of_department')
                            ->label('Is Head of Department')
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            // Add relation managers here if needed
            // 'courses' => CoursesRelationManager::class,
            // 'logbooks' => LogbooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            // 'view' => Pages\ViewStaff::route('/{record}'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'faculty', 'department']);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'faculty', 'department']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'staff_id',
            'user.first_name',
            'user.last_name',
            'user.email',
            'designation',
            'faculty.name',
            'department.name',
        ];
    }
}
