<?php

namespace App\Filament\Resources\Guesses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class GuessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.uid')
                    ->label('UID')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('用户名')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('stock.symbol')
                    ->label('Stock')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('guess_date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('guessed_price')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_correct')
                    ->boolean()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultGroup('guess_date')
            ->groups([
                \Filament\Tables\Grouping\Group::make('guess_date')
                    ->label('按日期分组')
                    ->date(),
                \Filament\Tables\Grouping\Group::make('user.email')
                    ->label('按用户分组'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_correct')
                    ->options([
                        '1' => '已中奖',
                        '0' => '未中奖',
                    ])
                    ->label('中奖状态'),
                \Filament\Tables\Filters\Filter::make('guess_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date')
                            ->label('选择日期'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['date'], fn($q) => $q->whereDate('guess_date', $data['date']));
                    })
                    ->label('竞猜日期'),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('direct_export')
                    ->label('Export the lottery data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (\Filament\Resources\Pages\ListRecords $livewire) {
                        $records = $livewire->getFilteredTableQuery()->with(['user', 'stock'])->get();

                        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
                        $csv->insertOne(['ID', 'User Email', 'User UID', 'User Name', 'Stock', 'Date', 'Price', 'Is Correct']);

                        foreach ($records as $record) {
                            $csv->insertOne([
                                $record->id,
                                $record->user->email ?? '',
                                $record->user->uid ?? '',
                                $record->user->name ?? '',
                                $record->stock->symbol ?? '',
                                $record->guess_date->toDateString(),
                                $record->guessed_price,
                                $record->is_correct ? 'Yes' : 'No',
                            ]);
                        }

                        return response()->streamDownload(function () use ($csv) {
                            echo $csv->toString();
                        }, 'guesses_' . now()->format('Y-m-d') . '.csv');
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
