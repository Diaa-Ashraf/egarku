<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\SubscribeRequest;
use App\Traits\ApiResponse;
use App\Interfaces\Services\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    // GET /api/plans
    public function plans()
    {
        return $this->success($this->paymentService->getPlans());
    }

    // POST /api/subscribe
    public function subscribe(SubscribeRequest $request)
    {
        try {
            $result = $this->paymentService->subscribe($request->validated(), auth()->id());
            return $this->success($result, 'تم إنشاء طلب الدفع');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    // POST /api/payment/paymob/callback
    // Paymob بيبعت الـ callback تلقائي
    public function paymobCallback(Request $request)
    {
        try {
            $this->paymentService->paymobCallback($request->all());
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Paymob callback error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    // POST /api/payment/fawry/callback
    public function fawryCallback(Request $request)
    {
        try {
            $this->paymentService->fawryCallback($request->all());
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Fawry callback error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
