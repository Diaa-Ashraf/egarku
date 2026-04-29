<?php

namespace App\Filament\Resources\Ads\Pages;

use App\Filament\Resources\Ads\AdResource;
use App\Models\AdImage;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAd extends EditRecord
{
    protected static string $resource = AdResource::class;

    protected array $images = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->images->pluck('path')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // احفظ الصور مؤقتاً وأزلها من البيانات
        $this->images = $data['images'] ?? [];
        unset($data['images']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (!empty($this->images)) {
            // احذف الصور القديمة
            AdImage::where('ad_id', $this->record->id)->delete();

            // أضف الصور الجديدة
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
