<?php

namespace App\Http\Controllers;

use App\Support\GlobalSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        return response()->json(['groups' => GlobalSearch::for($request->user(), (string) ($data['q'] ?? ''))]);
    }
}
