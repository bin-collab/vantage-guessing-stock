<?php

namespace App\Filament\Resources\ClosingPrices\Pages;

use App\Filament\Resources\ClosingPrices\ClosingPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClosingPrice extends EditRecord
{
    protected static string $resource = ClosingPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
