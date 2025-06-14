<?php

namespace App\Filament\Clusters\AccessControl\Resources;

use App\Filament\Clusters\AccessControl;
use App\Filament\Clusters\AccessControl\Resources\RoleResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Role;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $cluster = AccessControl::class;

    public static function getEloquentQuery(): Builder
    {
        // Show all roles with web guard
        return parent::getEloquentQuery()->where('guard_name', 'web');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    TextInput::make('identifier')
                        ->label('Display Name')
                        ->live()
                        ->afterStateUpdated(fn($state, $set) => $set('name', Str::slug($state)))
                        ->required(),

                    TextInput::make('name')
                        ->label('System Identifier')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Toggle::make('all_permission')
                        ->label('Grant All Permissions')
                        ->default(false)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                            if ($state) {
                                // When all permissions is enabled, clear specific permissions
                                $set('permissions', []);
                            }
                        }),

                    Select::make('permissions')
                        ->label('Permissions')
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn(Get $get) => !$get('all_permission'))
                        ->columnSpanFull()
                        ->options(function () {
                            // Group permissions by parent for better UX
                            $permissions = Permission::with('parent')->get();
                            $grouped = [];

                            foreach ($permissions as $permission) {
                                if ($permission->parent_id) {
                                    // Child permission - group under parent
                                    $parentName = $permission->parent->name ?? 'Other';
                                    $grouped[$parentName][$permission->id] = $permission->name;
                                } else {
                                    // Parent permission or standalone
                                    $grouped['Main Modules'][$permission->id] = $permission->name;
                                }
                            }

                            return $grouped;
                        })
                        ->searchable(['name', 'identifier']),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identifier')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'), // Handle null values

                TextColumn::make('name')
                    ->label('System Name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('all_permission')
                    ->label('All Permissions')
                    ->boolean()
                    ->default(false), // Handle null values

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
                    ->default(0),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users Count')
                    ->default(0),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('all_permission')
                    ->label('All Permissions')
                    ->boolean()
                    ->trueLabel('Has All Permissions')
                    ->falseLabel('Limited Permissions')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(Model $record) => !in_array($record->name, ['super-admin', 'admin'])), // Prevent deletion of core roles
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Filter out protected roles before deletion
                            $deletableRecords = $records->filter(function ($record) {
                                return !in_array($record->name, ['super-admin', 'admin']);
                            });
                            $deletableRecords->each->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        $canView = $user && $user->can('view-roles');



        return $canView;
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->can('view-roles');
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->can('create-roles');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('edit-roles');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('delete-roles') &&
            !in_array($record->name, ['super-admin', 'admin']);
    }

    public static function canView(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('view-roles');
    }
}
