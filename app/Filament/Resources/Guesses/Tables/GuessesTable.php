<?php

namespace App\Filament\Resources\Guesses\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use League\Csv\Writer;

class GuessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.uid')
                    ->label('UID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('用户名')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock.symbol')
                    ->label('Stock')
                    ->sortable(),
                TextColumn::make('guess_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('guessed_price')
                    ->sortable(),
                IconColumn::make('is_correct')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->defaultGroup('guess_date')
            ->defaultSort('guess_date', 'desc')
            ->groups([
                Group::make('guess_date')
                    ->label('按日期分组')
                    ->date(),
                Group::make('user.email')
                    ->label('按用户分组'),
            ])
            ->filters([
                SelectFilter::make('is_correct')
                    ->options([
                        '1' => '已中奖',
                        '0' => '未中奖',
                    ])
                    ->label('中奖状态'),
                Filter::make('guess_date')
                    ->form([
                        DatePicker::make('date')
                            ->label('选择日期'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['date'], fn($q) => $q->whereDate('guess_date', $data['date']));
                    })
                    ->label('竞猜日期'),
            ])
            ->headerActions([
                Action::make('direct_export')
                    ->label('Export the lottery data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (ListRecords $livewire) {
                        $records = $livewire->getFilteredTableQuery()->with(['user', 'stock'])->get();

                        $csv = Writer::createFromFileObject(new \SplTempFileObject);
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
