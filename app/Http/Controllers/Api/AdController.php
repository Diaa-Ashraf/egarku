<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ad\ContactAdRequest;
use App\Http\Requests\Ad\StoreAdRequest;
use App\Http\Requests\Ad\UpdateAdRequest;
use App\Traits\ApiResponse;
use App\Interfaces\Services\AdServiceInterface;
use Illuminate\Http\Request;

class AdController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AdServiceInterface $adService
    ) {}

    // GET /api/ads/{id}
    public function show(int $id, Request $request)
    {
        try {
            $data = $this->adService->show($id, auth()->id());
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->notFound($e->getMessage());
        }
    }

    // POST /api/ads
    public function store(StoreAdRequest $request)
    {
        try {
            $ad = $this->adService->store($request->validated(), auth()->id());
            return $this->created($ad, 'تم إرسال إعلانك للمراجعة');
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 401, 403, 404, 422, 500]) ? $e->getCode() : 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    // PUT /api/ads/{id}
    public function update(UpdateAdRequest $request, int $id)
    {
        try {
            $ad = $this->adService->update($id, $request->validated(), auth()->id());
            return $this->success($ad, 'تم التعديل وإعادة الإرسال للمراجعة');
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 401, 403, 404, 422, 500]) ? $e->getCode() : 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    // DELETE /api/ads/{id}
    public function destroy(int $id)
    {
        try {
            $this->adService->destroy($id, auth()->id());
            return $this->success(message: 'تم حذف الإعلان');
        } catch (\Exception $e) {
            $code = in_array($e->getCode(), [400, 401, 403, 404, 422, 500]) ? $e->getCode() : 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    // POST /api/ads/{id}/contact
    public function contact(ContactAdRequest $request, int $id)
    {
        $data = $this->adService->contact($id, $request->type, auth()->id(), $request->ip());
        return $this->success($data);
    }

    // POST /api/ads/{id}/save
    public function save(int $id)
    {
        $result = $this->adService->toggleSave($id, auth()->id());
        return $this->success($result);
    }

    // GET /api/saved-ads
    public function savedAds()
    {
        $ads = $this->adService->getSaved(auth()->id());
        return $this->paginated($ads);
    }
}
