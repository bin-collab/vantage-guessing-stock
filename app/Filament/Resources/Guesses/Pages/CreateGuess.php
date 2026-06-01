<?php

namespace App\Filament\Resources\Guesses\Pages;

use App\Filament\Resources\Guesses\GuessResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuess extends CreateRecord
{
    protected static string $resource = GuessResource::class;
}
