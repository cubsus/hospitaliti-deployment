<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\Resources\PaginationValues;
use App\Filament\Helpers\Resources\SearchOptionLimit;
use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Components;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Schemas;
use BackedEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?int $navigationSort = 13;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-finger-print';

    public static function showLast(Builder $query): Builder
    {
        $query = $query->orderBy('created_at', 'desc');

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::showLast(Activity::query())
            )
            ->columns([
                Tables\Columns\TextColumn::make('causer_info')
                    ->label('Causer')
                    ->translateLabel(),

                Tables\Columns\TextColumn::make('description')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'warning',
                    })
                    ->label('Activity')
                    ->translateLabel(),

                Tables\Columns\TextColumn::make('subject_info')
                    ->label('Subject')
                    ->translateLabel(),

                Tables\Columns\TextColumn::make('properties')
                    ->translateLabel(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('created_until')
                            ->translateLabel(),
                    ])
                    ->columnSpan(2)
                    ->columns(2)
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),

                Tables\Filters\SelectFilter::make('causer_id')
                    ->options(User::all()->mapWithKeys(fn($user) => [$user->id => $user->full_name ?? $user->email]))
                    ->query(function (Builder $query, array $data): Builder {
                        $causerId = $data['value'] ?? null;

                        return $query->when(
                            $causerId,
                            fn(): Builder => $query->whereHasMorph(
                                'causer',
                                [User::class],
                                function (Builder $query) use ($causerId): void {
                                    $query->where('id', $causerId);
                                }
                            )
                        );
                    })
                    ->searchable()
                    ->optionsLimit(SearchOptionLimit::getSearchOptionLimit())
                    ->preload()
                    ->label('Causer')
                    ->translateLabel(),

                Tables\Filters\SelectFilter::make('subject_type')
                    ->options([
                        \App\Models\User::class => 'User',
                        \App\Models\User::class => 'User',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $subjectType = $data['value'] ?? null;

                        return $query->when(
                            $subjectType,
                            fn(Builder $subQuery): Builder => $subQuery->where('subject_type', $subjectType)
                        );
                    })
                    ->label('Subject type')
                    ->translateLabel(),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->paginated(PaginationValues::getPaginationValues());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Activity');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activities');
    }

    public static function getNavigationGroup(): string
    {
        return __('Settings');
    }
}
