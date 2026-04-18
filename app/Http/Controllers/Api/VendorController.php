<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\Services\VendorServiceInterface;
use App\Http\Requests\Vendor\UpdateVendorRequest;

class VendorController extends Controller
{
    use ApiResponse;

    public function __construct(
        private VendorServiceInterface $vendorService
    ) {}

    // GET /api/vendors/{id}
    // صفحة بروفايل المعلن العام
    public function show(int $id)
    {
        try {
            $data = $this->vendorService->show($id);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    // PUT /api/vendor/profile
    // تعديل بروفايل المعلن (Dashboard)
    public function update(UpdateVendorRequest $request)
    {
        try {
            $vendor = $this->vendorService->update($request->validated(), auth()->id());
            return $this->success($vendor, 'تم تحديث بياناتك');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 400;
            return $this->error($e->getMessage(), $code);
        }
    }
}
