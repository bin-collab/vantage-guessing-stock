<?php

namespace App\Filament\Resources\OptInUsers\Pages;

use App\Filament\Resources\OptInUsers\OptInUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOptInUser extends CreateRecord
{
    protected static string $resource = OptInUserResource::class;
}
