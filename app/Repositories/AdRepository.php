<?php

namespace App\Repositories;

use App\Interfaces\AdRepositoryInterface;
use App\Models\Ad;
use Illuminate\Support\Facades\DB;

class AdRepository implements AdRepositoryInterface
{
    // تفاصيل إعلان كامل — eager loading عشان البيانات معقدة
    public function findById(int $id): ?object
    {
        return Ad::where('id', $id)
            ->where('status', 'active')
            ->with([
                'images:id,ad_id,path,is_main,sort_order',
                'area:id,name,city_id',
                'area.city:id,name',
                'category:id,name,slug,parent_id',
                'category.parent:id,name,slug',
                'marketplace:id,name,slug',
                'amenities:id,name,icon',
                'fieldValues.field:id,key,name,type',
                'vendorProfile:id,user_id,display_name,company_name,whatsapp,work_phone,avg_rating,reviews_count,is_verified,vendor_type',
                'vendorProfile.user:id,name,avatar',
            ])
            ->first();
    }

    public function create(array $data): object
    {
        return Ad::create($data);
    }

    public function update(int $id, array $data): object
    {
        $ad = Ad::findOrFail($id);
        $ad->update($data);
        return $ad->fresh();
    }

    public function delete(int $id): bool
    {
        return Ad::findOrFail($id)->delete();
    }

    // increment مباشرة بدون load + save
    public function incrementViews(int $id): void
    {
        DB::table('ads')->where('id', $id)->increment('views_count');
    }

    public function incrementContacts(int $id): void
    {
        DB::table('ads')->where('id', $id)->increment('contacts_count');
    }
}
