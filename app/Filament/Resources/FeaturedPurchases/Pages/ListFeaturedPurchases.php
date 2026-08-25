<?php

namespace App\Filament\Resources\FeaturedPurchases\Pages;

use App\Filament\Resources\FeaturedPurchases\FeaturedPurchaseResource;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedPurchases extends ListRecords
{
    protected static string $resource = FeaturedPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
