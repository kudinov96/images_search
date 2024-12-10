<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\ImageSearchElasticService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class ImageController extends Controller
{
    public function search(SearchRequest $request, ImageSearchElasticService $imageSearchElasticService)
    {
        $searchQuery = $request->input('search');
        $page = $request->input('page', 1);
        $perPage = 100;

        $results = $imageSearchElasticService->searchImagesByTags($searchQuery, $page, $perPage);

        $paginator = new LengthAwarePaginator(
            $results['data'],
            $results['total'],
            $results['perPage'],
            $results['currentPage'],
            ['path' => $request->url()]
        );

        $paginator->withQueryString();

        return view('search', [
            'search' => $searchQuery,
            'results' => $paginator,
        ]);
    }
}
