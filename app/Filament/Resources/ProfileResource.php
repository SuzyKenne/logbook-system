<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Staff;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProfileResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Staff Profiles';

    protected static ?string $modelLabel = 'Staff Profile';

    protected static ?string $pluralModelLabel = 'Staff Profiles';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->description('Basic staff member information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_image')
                            ->label('Profile Image')
                            ->image()
                            ->avatar()
                            ->directory('staff-profiles')
                            ->disk('public')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->maxSize(2048) // 2MB max
                            // ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull()
                            ->helperText('Upload a professional profile photo. Recommended size: 400x400px'),

                        // Forms\Components\TextInput::make('profile_image_alt')
                        //     ->label('Image Alt Text')
                        //     ->maxLength(255)
                        //     ->placeholder('Brief description of the image for accessibility')
                        //     ->columnSpanFull()
                        //     ->helperText('Describe the image for screen readers and accessibility'),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User Account')
                                    ->relationship('user', 'email')
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
                                            ->unique(User::class, 'email'),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('staff_id')
                                    ->label('Staff ID')
                                    ->required()
                                    ->unique(Staff::class, 'staff_id', ignoreRecord: true)
                                    ->maxLength(50),

                                Forms\Components\DatePicker::make('hire_date')
                                    ->label('Date of Hire')
                                    ->required()
                                    ->default(now()),
                            ]),
                    ]),

                Section::make('Academic Information')
                    ->description('Faculty, department, and academic details')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('faculty_id')
                                    ->label('Faculty')
                                    ->relationship('faculty', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set) => $set('department_id', null)),

                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship(
                                        'department',
                                        'name',
                                        fn(Builder $query, callable $get) =>
                                        $get('faculty_id')
                                            ? $query->where('faculty_id', $get('faculty_id'))
                                            : $query
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
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
                                    ->reactive()
                                    ->visible(fn(callable $get) => $get('staff_type') === 'dean'),

                                Forms\Components\Toggle::make('is_head_of_department')
                                    ->label('Is Head of Department')
                                    ->reactive()
                                    ->visible(fn(callable $get) => $get('staff_type') === 'hod'),
                            ]),
                    ]),

                Section::make('Professional Details')
                    ->description('Qualifications, specialization, and designation')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('designation')
                                    ->label('Designation/Title')
                                    ->maxLength(255)
                                    ->placeholder('e.g., Professor, Associate Professor, Assistant Professor'),

                                Forms\Components\Select::make('status')
                                    ->label('Employment Status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended',
                                    ])
                                    ->required()
                                    ->default('active'),
                            ]),

                        Forms\Components\Textarea::make('qualification')
                            ->label('Qualifications')
                            ->rows(3)
                            ->placeholder('List educational qualifications, degrees, certifications...'),

                        Forms\Components\Textarea::make('specialization')
                            ->label('Areas of Specialization')
                            ->rows(3)
                            ->placeholder('Research interests, teaching specializations...'),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biography')
                            ->rows(4)
                            ->placeholder('Professional biography, achievements, experience...'),
                    ]),

                Section::make('Contact & Office Information')
                    ->description('Office location and contact details')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('office_location')
                                    ->label('Office Location')
                                    ->maxLength(255)
                                    ->placeholder('Building, Room Number'),

                                Forms\Components\TextInput::make('office_phone')
                                    ->label('Office Phone')
                                    ->tel()
                                    ->maxLength(255),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->getDefaultProfileImage())
                    ->size(40),

                TextColumn::make('staff_id')
                    ->label('Staff ID')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('user.first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('faculty.name')
                    ->label('Faculty')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                BadgeColumn::make('staff_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'dean',
                        'warning' => 'hod',
                        'primary' => 'lecturer',
                    ])
                    ->icons([
                        'heroicon-o-star' => 'dean',
                        'heroicon-o-user-group' => 'hod',
                        'heroicon-o-academic-cap' => 'lecturer',
                    ]),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable()
                    ->toggleable()
                    ->wrap(),

                BooleanColumn::make('is_dean')
                    ->label('Dean')
                    ->toggleable(isToggledHiddenByDefault: true),

                BooleanColumn::make('is_head_of_department')
                    ->label('HOD')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'suspended',
                        'warning' => 'inactive',
                    ]),

                TextColumn::make('hire_date')
                    ->label('Hire Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('office_location')
                    ->label('Office')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
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

                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('staff_type')
                    ->label('Staff Type')
                    ->options([
                        'dean' => 'Dean',
                        'hod' => 'Head of Department',
                        'lecturer' => 'Lecturer',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                Filter::make('is_dean')
                    ->label('Deans Only')
                    ->query(fn(Builder $query): Builder => $query->where('is_dean', true)),

                Filter::make('is_hod')
                    ->label('HODs Only')
                    ->query(fn(Builder $query): Builder => $query->where('is_head_of_department', true)),
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
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // You can add relation managers here if needed
            // For example: CoursesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'view' => Pages\ViewProfile::route('/{record}'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'staff_id',
            'user.first_name',
            'user.last_name',
            'user.email',
            'designation',
            'department.name',
            'faculty.name',
        ];
    }
}
