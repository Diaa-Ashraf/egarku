<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PurchaseServiceRequest;
use App\Traits\ApiResponse;
use App\Interfaces\Services\PaymentServiceInterface;

class ServiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    // GET /api/services/pricing
    public function pricing()
    {
        return $this->success($this->paymentService->getServicePricing());
    }

    // POST /api/services/purchase
    public function purchase(PurchaseServiceRequest $request)
    {
        try {
            $result = $this->paymentService->purchaseService(
                $request->validated(),
                auth()->id()
            );
            return $this->success($result, 'تم إنشاء طلب الدفع');
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 401, 403, 404, 422, 500]) ? $e->getCode() : 400;
            return $this->error($e->getMessage(), $code);
        }
    }
}
