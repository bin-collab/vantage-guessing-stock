<?php

namespace App\Filament\Resources\ClosingPrices\Pages;

use App\Filament\Resources\ClosingPrices\ClosingPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClosingPrices extends ListRecords
{
    protected static string $resource = ClosingPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('batch_create')
                ->label('录入每日收盘价')
                ->icon('heroicon-o-plus-circle')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date')
                        ->label('收盘日期')
                        ->default(now())
                        ->required(),
                    \Filament\Schemas\Components\Grid::make(3)
                        ->schema(function () {
                            return \App\Models\Stock::all()->map(function ($stock) {
                                return \Filament\Forms\Components\TextInput::make('prices.' . $stock->id)
                                    ->label($stock->name . ' (' . $stock->symbol . ')')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01);
                            })->toArray();
                        }),
                ])
                ->action(function (array $data): void {
                    foreach ($data['prices'] as $stockId => $price) {
                        \App\Models\ClosingPrice::updateOrCreate(
                            [
                                'stock_id' => $stockId,
                                'date' => $data['date'],
                            ],
                            [
                                'price' => $price,
                            ]
                        );
                    }
                    \Filament\Notifications\Notification::make()
                        ->title('每日收盘价已保存，校验逻辑已自动运行。')
                        ->success()
                        ->send();
                }),
            // CreateAction::make()->label('新增单条记录'),
        ];
    }
}
