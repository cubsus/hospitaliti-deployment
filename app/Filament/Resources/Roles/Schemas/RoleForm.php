<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas;
use Filament\Forms;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Schemas\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->minLength(3)
                        ->maxLength(255)
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->translateLabel(),
                ]),

                Schemas\Components\Section::make()->schema([
                    Forms\Components\CheckboxList::make('permissions')
                        ->relationship(
                            name: 'permissions',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('name', 'not like', '%permission%')
                        )
                        ->getOptionLabelFromRecordUsing(function (Model $record) {

                            /** @var \Spatie\Permission\Models\Permission */
                            $permission = $record;

                            $name = $permission->name;
                            $model = ucfirst(Str::before($name, '.'));
                            $permission = Str::after($name, '.');

                            return $model.' - '.$permission;
                        })
                        ->bulkToggleable()
                        ->searchable()
                        ->searchPrompt('Search for a permission')
                        ->noSearchResultsMessage('No permissions found.')
                        ->searchDebounce(500)
                        ->columns(4)
                        ->translateLabel(),
                ])->columnSpanFull(),
            ]);
    }
}
