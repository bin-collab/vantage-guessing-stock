<?php

namespace App\Filament\Resources\OptInUsers\Pages;

use App\Filament\Resources\OptInUsers\OptInUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOptInUser extends EditRecord
{
    protected static string $resource = OptInUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
