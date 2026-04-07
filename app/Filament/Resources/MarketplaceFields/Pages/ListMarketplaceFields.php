<?php

namespace App\Filament\Resources\MarketplaceFields\Pages;

use App\Filament\Resources\MarketplaceFields\MarketplaceFieldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaceFields extends ListRecords
{
    protected static string $resource = MarketplaceFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
