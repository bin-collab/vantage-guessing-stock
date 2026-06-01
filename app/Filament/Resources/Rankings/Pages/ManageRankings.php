<?php

namespace App\Filament\Resources\Rankings\Pages;

use App\Filament\Resources\Rankings\RankingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRankings extends ManageRecords
{
    protected static string $resource = RankingResource::class;

    public function getTitle(): string
    {
        return 'Rankings';
    }

    // public function getBreadcrumbs(): array
    // {
    //     return [
    //         RankingResource::getUrl() => 'Rankings',
    //     ];
    // }

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
