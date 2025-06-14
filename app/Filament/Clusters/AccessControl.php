<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class AccessControl extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 2;
}
