<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(
        private WhatsAppWebhookService $webhookService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        Log::info('WhatsApp Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
        ]);

        try {
            $result = $this->webhookService->handle($request->all());

            Log::info('WhatsApp Webhook Processed', ['result' => $result]);

            $statusCode = $result['code'] ?? 200;

            return response()->json($result, $statusCode);
        } catch (\Throwable $e) {
            Log::error('WhatsApp Webhook Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}
