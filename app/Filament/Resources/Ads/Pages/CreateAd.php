<?php

namespace App\Filament\Resources\Ads\Pages;

use App\Filament\Resources\Ads\AdResource;
use App\Models\AdImage;
use Filament\Resources\Pages\CreateRecord;

class CreateAd extends CreateRecord
{
    protected static string $resource = AdResource::class;

    protected array $images = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // احفظ الصور مؤقتاً وأزلها من البيانات
        $this->images = $data['images'] ?? [];
        unset($data['images']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->images)) {
            foreach ($this->images as $index => $imagePath) {
                AdImage::create([
                    'ad_id'       => $this->record->id,
                    'path'        => $imagePath,
                    'is_main'     => $index === 0,
                    'sort_order'  => $index,
                ]);
            }
        }
    }
}
