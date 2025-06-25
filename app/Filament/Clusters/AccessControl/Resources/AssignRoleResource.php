<?php

namespace App\Filament\Clusters\AccessControl\Resources;

use App\Filament\Clusters\AccessControl;
use App\Filament\Clusters\AccessControl\Resources\AssignRoleResource\Pages;
use App\Filament\Clusters\AccessControl\Resources\AssignRoleResource\RelationManagers;
use App\Models\AssignRole;
use App\Models\UserAssignment;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class AssignRoleResource extends Resource
{
    protected static ?string $model = UserAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = AccessControl::class;

    protected static ?string $navigationLabel = 'Assign Roles';

    protected static ?string $modelLabel = 'User Assignment';

    protected static ?string $pluralModelLabel = 'User Assignments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a user'),

                        Select::make('faculty_id')
                            ->label('Faculty')
                            ->relationship('faculty', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (callable $set) {
                                $set('department_id', null);
                            })
                            ->required()
                            ->placeholder('Select a faculty'),

                        Select::make('department_id')
                            ->label('Department')
                            ->relationship(
                                'department',
                                'name',
                                fn(Builder $query, callable $get) =>
                                $query->where('faculty_id', $get('faculty_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select a department')
                            ->disabled(fn(callable $get) => !$get('faculty_id')),

                        Select::make('assignment_type')
                            ->label('Assignment Type')
                            ->options([
                                'admin' => 'Administrator',
                                'faculty_admin' => 'Faculty Administrator',
                                'department_admin' => 'Department Administrator',
                                'coordinator' => 'Coordinator',
                                'supervisor' => 'Supervisor',
                                'staff' => 'Staff',
                            ])
                            ->required()
                            ->placeholder('Select assignment type'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Duration')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->default(now())
                            ->before('end_date'),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->after('start_date')
                            ->placeholder('Leave empty for indefinite assignment'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('faculty.name')
                    ->label('Faculty')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                BadgeColumn::make('assignment_type')
                    ->label('Assignment Type')
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'faculty_admin',
                        'success' => 'department_admin',
                        'primary' => 'coordinator',
                        'secondary' => 'supervisor',
                        'gray' => 'staff',
                    ]),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable()
                    ->placeholder('Indefinite'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                        'warning' => 'suspended',
                        'gray' => 'expired',
                    ]),

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

                SelectFilter::make('assignment_type')
                    ->label('Assignment Type')
                    ->options([
                        'admin' => 'Administrator',
                        'faculty_admin' => 'Faculty Administrator',
                        'department_head' => 'Department Administrator',
                        'lecturer' => 'Staff',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'expired' => 'Expired',
                    ]),
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
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'active']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['status' => 'inactive']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignRoles::route('/'),
            'create' => Pages\CreateAssignRole::route('/create'),
            'edit' => Pages\EditAssignRole::route('/{record}/edit'),
        ];
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::where('status', 'active')->count();
    // }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
