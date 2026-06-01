<?php

namespace App\Filament\Imports;

use App\Models\OptInUser;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class OptInUserImporter extends Importer
{
    protected static ?string $model = OptInUser::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('email')
                ->requiredMapping()
                ->rules(['required', 'email']),
            ImportColumn::make('uid')
                ->label('UID')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('name')
                ->label('用户名')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): OptInUser
    {
        return OptInUser::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your opt in user import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
