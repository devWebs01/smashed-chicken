<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(
        private WhatsAppWebhookService $webhookService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->webhookService->handle($request->all());

        $statusCode = $result['code'] ?? 200;

        return response()->json($result, $statusCode);
    }
}
