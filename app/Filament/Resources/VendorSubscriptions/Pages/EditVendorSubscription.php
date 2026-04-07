<?php

namespace App\Filament\Resources\VendorSubscriptions\Pages;

use App\Filament\Resources\VendorSubscriptions\VendorSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVendorSubscription extends EditRecord
{
    protected static string $resource = VendorSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
