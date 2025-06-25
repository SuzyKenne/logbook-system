<?php

// App/Filament/Resources/ProfileResource/Pages/ListProfiles.php
namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProfiles extends ListRecords
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('success'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Staff')
                ->badge(fn() => $this->getModel()::count()),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'active'))
                ->badge(fn() => $this->getModel()::where('status', 'active')->count()),

            'deans' => Tab::make('Deans')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_dean', true))
                ->badge(fn() => $this->getModel()::where('is_dean', true)->count()),

            'hods' => Tab::make('HODs')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_head_of_department', true))
                ->badge(fn() => $this->getModel()::where('is_head_of_department', true)->count()),

            'lecturers' => Tab::make('Lecturers')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('staff_type', 'lecturer'))
                ->badge(fn() => $this->getModel()::where('staff_type', 'lecturer')->count()),
        ];
    }
}
