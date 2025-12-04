<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Helpers\Resources\SearchOptionLimit;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
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
                                    ->required(fn (Page $livewire): bool => $livewire instanceof CreateRecord)
                                    ->minLength(8)->same('passwordConfirmation')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->translateLabel(),

                                Forms\Components\TextInput::make('passwordConfirmation')
                                    ->password()
                                    ->revealable()
                                    ->label('Password confirmation')
                                    ->required(fn (Page $livewire): bool => $livewire instanceof CreateRecord)
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
                                    ->required()
                                    ->relationship('roles', 'name')
                                    ->searchable()
                                    ->multiple()
                                    ->optionsLimit(SearchOptionLimit::getSearchOptionLimit())
                                    ->preload()
                                    ->columnStart(1)
                                    ->translateLabel(),

                            ])->columns(2),

                    ])
                    ->columnSpan(1),

            ])->columns(2);
    }
}
