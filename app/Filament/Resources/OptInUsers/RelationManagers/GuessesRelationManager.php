<?php

namespace App\Filament\Resources\OptInUsers\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuessesRelationManager extends RelationManager
{
    protected static string $relationship = 'guesses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stock.symbol')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('stock.symbol')
            ->columns([
                TextColumn::make('stock.symbol')
                    ->label('股票'),
                TextColumn::make('guess_date')
                    ->label('日期')
                    ->date(),
                TextColumn::make('guessed_price')
                    ->label('竞猜价格'),
                \Filament\Tables\Columns\IconColumn::make('is_correct')
                    ->label('是否中奖')
                    ->boolean(),
            ])
            ->defaultGroup('guess_date')
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
