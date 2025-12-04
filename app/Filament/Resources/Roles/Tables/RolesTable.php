<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Helpers\Resources\PaginationValues;
use Filament\Tables\Table;
use Filament\Tables;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->translateLabel(),
            ])
            ->paginated(PaginationValues::getPaginationValues());
    }
}
