<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $lengthAwarePaginator = Product::query()
            ->when($request->search, function ($query, $search): void {
                $query->where('name', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('description', 'like', sprintf('%%%s%%', $search));
            })
            ->when($request->sort_by, function ($query, $sortBy): void {
                $direction = $request->get('sort_direction', 'asc');
                $query->orderBy($sortBy, $direction);
            }, function ($query): void {
                $query->orderBy('name', 'asc');
            })
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => $lengthAwarePaginator->items(),
                'pagination' => [
                    'current_page' => $lengthAwarePaginator->currentPage(),
                    'per_page' => $lengthAwarePaginator->perPage(),
                    'total' => $lengthAwarePaginator->total(),
                    'last_page' => $lengthAwarePaginator->lastPage(),
                    'has_more' => $lengthAwarePaginator->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => [
                'product' => $product,
            ],
        ]);
    }
}
