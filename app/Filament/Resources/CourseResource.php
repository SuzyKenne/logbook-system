<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\Department;
use App\Models\Level;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('level_id')
                                    ->label('Level')
                                    ->relationship('level', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('course_code')
                                    ->label('Course Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->placeholder('e.g., CS101')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('course_name')
                                    ->label('Course Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Introduction to Computer Science')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Course Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Brief description of the course content and objectives'),
                    ]),

                Forms\Components\Section::make('Course Details')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('credit_hours')
                                    ->label('Credit Hours')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->default(3)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('contact_hours')
                                    ->label('Contact Hours')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->default(3)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('course_type')
                                    ->label('Course Type')
                                    ->options([
                                        'core' => 'Core',
                                        'elective' => 'Elective',
                                        'general' => 'General Education',
                                        'major' => 'Major Requirement',
                                        'minor' => 'Minor Requirement',
                                        'prerequisite' => 'Prerequisite',
                                    ])
                                    ->required()
                                    ->default('core')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('semester')
                                    ->label('Semester')
                                    ->options([
                                        'first' => 'First Semester',
                                        'second' => 'Second Semester',
                                        'summer' => 'Summer Session',
                                    ])
                                    ->required()
                                    ->default('first')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('academic_year')
                                    ->label('Academic Year')
                                    ->numeric()
                                    ->required()
                                    ->minValue(2020)
                                    ->maxValue(2050)
                                    ->default(date('Y'))
                                    ->columnSpan(1),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required()
                                    ->default('active')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('course_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                Tables\Columns\TextColumn::make('course_name')
                    ->label('Course Name')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable()
                    ->badge()
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('credit_hours')
                    ->label('Credits')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('course_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'core' => 'danger',
                        'elective' => 'warning',
                        'general' => 'info',
                        'major' => 'success',
                        'minor' => 'secondary',
                        'prerequisite' => 'primary',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'first' => 'First',
                        'second' => 'Second',
                        'summer' => 'Summer',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('academic_year')
                    ->label('Year')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
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

                Tables\Filters\SelectFilter::make('course_type')
                    ->label('Course Type')
                    ->options([
                        'core' => 'Core',
                        'elective' => 'Elective',
                        'general' => 'General Education',
                        'major' => 'Major Requirement',
                        'minor' => 'Minor Requirement',
                        'prerequisite' => 'Prerequisite',
                    ]),

                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        'first' => 'First Semester',
                        'second' => 'Second Semester',
                        'summer' => 'Summer Session',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('academic_year')
                    ->form([
                        Forms\Components\TextInput::make('year')
                            ->label('Academic Year')
                            ->numeric()
                            ->placeholder('e.g., 2024'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year'],
                                fn(Builder $query, $year): Builder => $query->where('academic_year', $year),
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
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogbooksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
