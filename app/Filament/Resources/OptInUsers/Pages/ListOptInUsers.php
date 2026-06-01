<?php

namespace App\Filament\Resources\OptInUsers\Pages;

use App\Filament\Resources\OptInUsers\OptInUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOptInUsers extends ListRecords
{
    protected static string $resource = OptInUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
