<?php

namespace App\Repositories;

use App\Interfaces\PaymentRepositoryInterface;
use App\Models\Transaction;
use App\Models\VendorSubscription;
use Illuminate\Support\Facades\DB;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function createTransaction(array $data): object
    {
        return Transaction::create($data);
    }

    public function findTransaction(int $id): ?object
    {
        return Transaction::find($id);
    }

    public function findTransactionByReference(string $reference): ?object
    {
        return Transaction::where('reference', $reference)->first();
    }

    public function updateTransaction(int $id, array $data): void
    {
        DB::table('transactions')->where('id', $id)->update($data);
    }

    // إنشاء اشتراك جديد
    public function createSubscription(array $data): object
    {
        return VendorSubscription::create($data);
    }

    // إلغاء الاشتراكات النشطة القديمة
    public function cancelActiveSubscriptions(int $vendorId): void
    {
        DB::table('vendor_subscriptions')
            ->where('vendor_profile_id', $vendorId)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);
    }
}
