<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Interfaces\Services\SearchServiceInterface;
use App\Traits\ApiResponse;

class SearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private SearchServiceInterface $searchService
    ) {}

    // GET /api/search?q=شقة+في+اسكندرية&page=1
    public function index(SearchRequest $request)
    {
        $result = $this->searchService->search(
            query: $request->validated('q'),
            perPage: 20
        );

        return $this->success($result);
    }
}
