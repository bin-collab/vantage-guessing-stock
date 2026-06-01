<?php

namespace App\Filament\Exports;

use App\Models\Guess;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class GuessExporter extends Exporter
{
    protected static ?string $model = Guess::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.email')
                ->label('User Email'),
            ExportColumn::make('user.uid')
                ->label('User UID'),
            ExportColumn::make('stock.symbol')
                ->label('Stock Symbol'),
            ExportColumn::make('guess_date')
                ->label('Guess Date'),
            ExportColumn::make('guessed_price')
                ->label('Guessed Price'),
            ExportColumn::make('is_correct')
                ->label('Is Correct')
                ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your guess export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
