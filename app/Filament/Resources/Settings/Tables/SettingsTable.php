<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guess_start_date')
                    ->label('竞猜开始日期')
                    ->date()
                    ->sortable(),
                TextColumn::make('guess_end_date')
                    ->label('竞猜结束日期')
                    ->date()
                    ->sortable(),
                TextColumn::make('daily_deadline')
                    ->label('每日截止时间 (GMT+3)')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
