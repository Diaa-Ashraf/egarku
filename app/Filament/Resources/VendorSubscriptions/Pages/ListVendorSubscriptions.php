<?php

namespace App\Filament\Resources\VendorSubscriptions\Pages;

use App\Filament\Resources\VendorSubscriptions\VendorSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVendorSubscriptions extends ListRecords
{
    protected static string $resource = VendorSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
