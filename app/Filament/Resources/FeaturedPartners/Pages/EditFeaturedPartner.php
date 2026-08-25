<?php

namespace App\Filament\Resources\FeaturedPartners\Pages;

use App\Filament\Resources\FeaturedPartners\FeaturedPartnerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedPartner extends EditRecord
{
    protected static string $resource = FeaturedPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
