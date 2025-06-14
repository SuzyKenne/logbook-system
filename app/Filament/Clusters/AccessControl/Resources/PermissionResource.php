<?php

namespace App\Filament\Clusters\AccessControl\Resources;

use App\Filament\Clusters\AccessControl;
use App\Filament\Clusters\AccessControl\Resources\PermissionResource\Pages;
use App\Models\Permission;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $cluster = AccessControl::class;
    protected static ?string $navigationLabel = 'Permissions';
    protected static ?string $slug = 'permissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Permission Name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('identifier')
                        ->label('Display Name')
                        ->maxLength(255),

                    Select::make('parent_id')
                        ->label('Parent Permission')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    TextInput::make('guard_name')
                        ->label('Guard')
                        ->default('web')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('identifier')
                    ->label('Display Name')
                    ->searchable()
                    ->default('N/A'),

                TextColumn::make('parent.name')
                    ->label('Parent')
                    ->searchable()
                    ->default('N/A'),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
            // 'view' => Pages\ViewPermission::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->can('view-permissions');
    }

    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->can('view-permissions');
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->can('create-permissions');
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('edit-permissions');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('delete-permissions');
    }

    public static function canView(Model $record): bool
    {
        return Auth::check() && Auth::user()->can('view-permissions');
    }
}
