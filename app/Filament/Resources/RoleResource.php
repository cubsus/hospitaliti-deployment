<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\Resources\PaginationValues;
use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Forms;
use Filament\Schemas;
use BackedEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->translateLabel(),
            ])
            ->paginated(PaginationValues::getPaginationValues());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Roles');
    }

    public static function getNavigationGroup(): string
    {
        return __('Settings');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('name', '!=', 'Super Admin');
    }
}
