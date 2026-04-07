<?php

namespace App\Filament\Resources\MarketplaceFields\Pages;

use App\Filament\Resources\MarketplaceFields\MarketplaceFieldResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceField extends EditRecord
{
    protected static string $resource = MarketplaceFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
