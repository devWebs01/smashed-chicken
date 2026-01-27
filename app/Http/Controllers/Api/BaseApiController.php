<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     */
    protected function sendSuccess($data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send an error response.
     *
     * @param  mixed  $errors
     */
    protected function sendError(string $message = 'An error occurred', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Send a paginated response.
     *
     * @param  mixed  $items
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $paginator
     */
    protected function sendPaginated($items, $paginator, string $message = 'Data retrieved successfully', array $meta = []): JsonResponse
    {
        return $this->sendSuccess([
            'items' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'meta' => $meta,
        ], $message);
    }
}
