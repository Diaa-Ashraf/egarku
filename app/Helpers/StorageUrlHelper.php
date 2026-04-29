<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageUrlHelper
{
    /**
     * تحويل مسار نسبي إلى URL كامل من الـ public disk
     * لو المسار null بيرجع null
     * لو المسار URL كامل أصلاً بيرجعه زي ما هو
     */
    public static function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // لو URL كامل أصلاً، رجعه زي ما هو
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // تنظيف المسار من أي كلمة storage مكررة لو كانت محفوظة بالغلط في الداتا بيز
        $cleanPath = preg_replace('#^/?storage/#', '', ltrim($path, '/'));

        // نستخدم asset('storage/...') عشان يكون مطابق للي بيحصل في الداش بورد
        // لأن asset() بيتعامل بذكاء مع الروابط في حالة استضافة cPanel أو مسارات الـ public
        return asset('storage/' . $cleanPath);
    }

    /**
     * تحويل حقل صورة في كائن stdClass
     * بيعدل الكائن مباشرة ويرجعه
     */
    public static function transformField(object $item, string $field): object
    {
        if (isset($item->{$field})) {
            $item->{$field} = self::url($item->{$field});
        }
        return $item;
    }

    /**
     * تحويل حقول صور متعددة في كائن واحد
     */
    public static function transformFields(object $item, array $fields): object
    {
        foreach ($fields as $field) {
            self::transformField($item, $field);
        }
        return $item;
    }

    /**
     * تحويل حقل صورة في مجموعة من الكائنات (Collection)
     */
    public static function transformCollection($collection, string|array $fields): mixed
    {
        $fieldsArray = is_array($fields) ? $fields : [$fields];

        return $collection->transform(function ($item) use ($fieldsArray) {
            return self::transformFields($item, $fieldsArray);
        });
    }
}
