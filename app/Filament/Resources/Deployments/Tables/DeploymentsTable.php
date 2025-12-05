<?php

namespace App\Filament\Resources\Deployments\Tables;

use App\Filament\Helpers\Resources\PaginationValues;
use App\Enums\DeploymentStatusEnum;
use App\Models\Deployment;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Tables;

use Illuminate\Database\Eloquent\Builder;

class DeploymentsTable
{
    public static function showLast(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                static::showLast(Deployment::query())
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DeploymentStatusEnum::RUNNING->value => 'info',
                        DeploymentStatusEnum::COMPLETED->value => 'success',
                        DeploymentStatusEnum::FAILED->value => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed At')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('exit_code')
                    ->label('Exit Code')
                    ->badge()
                    ->color(fn (?int $state): string => match ($state) {
                        0 => 'success',
                        null => 'gray',
                        default => 'danger',
                    }),
            ])
            ->recordActions([
                Actions\Action::make('view')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->modalHeading('Deployment Details')
                    ->modalContent(function (Deployment $record) {
                        return view('filament.resources.deployments.view-deployment', [
                            'deployment' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(PaginationValues::getPaginationValues());
    }
}
