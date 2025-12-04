<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Helpers\Resources\PaginationValues;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->translateLabel(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->translateLabel()
                    ->label('Role'),

                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->translateLabel(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->translateLabel()
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(PaginationValues::getPaginationValues());
    }
}
