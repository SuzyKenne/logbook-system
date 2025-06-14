<?php

namespace App\Filament\Clusters\AccessControl\Resources;

use App\Filament\Clusters\AccessControl;
use App\Filament\Clusters\AccessControl\Resources\AssignRoleResource\Pages;
use App\Filament\Clusters\AccessControl\Resources\AssignRoleResource\RelationManagers;
use App\Models\AssignRole;
use App\Models\UserAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignRoleResource extends Resource
{
    protected static ?string $model = UserAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = AccessControl::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
}
