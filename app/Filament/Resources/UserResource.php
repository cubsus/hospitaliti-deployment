<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\Resources\PaginationValues;
use App\Filament\Helpers\Resources\SearchOptionLimit;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Forms;
use Filament\Schemas;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 7;

    public static function canViewAny(): bool
    {
        return authUserIsSuperAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema

            ->components([

                Schemas\Components\Group::make()

                    ->schema([

                        Schemas\Components\Section::make()

                            ->schema([

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->label('Email address')
                                    ->translateLabel()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn(Page $livewire): bool => $livewire instanceof CreateRecord)
                                    ->minLength(8)->same('passwordConfirmation')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('passwordConfirmation')
                                    ->password()
                                    ->revealable()
                                    ->label('Password confirmation')
                                    ->required(fn(Page $livewire): bool => $livewire instanceof CreateRecord)
                                    ->minLength(8)
                                    ->dehydrated(false)
                                    ->translateLabel(),

                            ])->columns(2),

                    ])->columnSpan(1),

                Schemas\Components\Group::make()

                    ->schema([

                        Schemas\Components\Section::make()

                            ->schema([

                                Forms\Components\Select::make('role')
                                    ->relationship('roles', 'name')
                                    ->searchable()
                                    ->optionsLimit(SearchOptionLimit::getSearchOptionLimit())
                                    ->preload()
                                    ->columnStart(1)
                                    ->translateLabel(),

                            ])->columns(2),

                    ])
                    ->hidden(fn($record): bool => static::userExistsAndIsSuperAdmin($record))
                    ->columnSpan(1),

            ])->columns(2);
    }

    public static function table(Table $table): Table
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function getNavigationGroup(): string
    {
        return __('Social');
    }

    public static function userExistsAndIsSuperAdmin($record): bool
    {
        /** @var \App\Models\User */
        $user = $record;

        if (($user != null) && ($user->hasRole('Super Admin'))) {
            return true;
        }

        return false;
    }
}
