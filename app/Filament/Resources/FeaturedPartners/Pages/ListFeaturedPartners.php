<?php

namespace App\Filament\Resources\FeaturedPartners\Pages;

use App\Filament\Resources\FeaturedPartners\FeaturedPartnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedPartners extends ListRecords
{
    protected static string $resource = FeaturedPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
