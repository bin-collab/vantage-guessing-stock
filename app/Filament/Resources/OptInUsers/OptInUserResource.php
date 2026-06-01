<?php

namespace App\Filament\Resources\OptInUsers;

use App\Filament\Resources\OptInUsers\Pages\CreateOptInUser;
use App\Filament\Resources\OptInUsers\Pages\EditOptInUser;
use App\Filament\Resources\OptInUsers\Pages\ListOptInUsers;
use App\Filament\Resources\OptInUsers\Schemas\OptInUserForm;
use App\Filament\Resources\OptInUsers\Tables\OptInUsersTable;
use App\Models\OptInUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OptInUserResource extends Resource
{
    protected static ?string $model = OptInUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OptInUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OptInUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GuessesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOptInUsers::route('/'),
            'create' => CreateOptInUser::route('/create'),
            'edit' => EditOptInUser::route('/{record}/edit'),
        ];
    }
}
