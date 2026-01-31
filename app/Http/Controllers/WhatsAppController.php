<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function __construct(
        private readonly WhatsAppWebhookService $whatsAppWebhookService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Handle GET request - return webhook status
        if ($request->isMethod('GET')) {
            return response()->json([
                'status' => 'active',
                'message' => 'WhatsApp Webhook is ready',
                'supported_methods' => ['GET', 'POST'],
                'endpoint' => '/webhook/whatsapp',
                'description' => 'Send POST request with Fonnte webhook payload',
            ], 200);
        }

        Log::info('WhatsApp Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'method' => $request->method(),
        ]);

        try {
            $result = $this->whatsAppWebhookService->handle($request->all());

            Log::info('WhatsApp Webhook Processed', ['result' => $result]);

            // CRITICAL: Always return 200 for Fonnte webhook
            // Fonnte will retry 15 times (every minute) if status is not 200
            // Even if processing fails, return 200 to prevent retry spam
            return response()->json($result, 200);

        } catch (\Throwable $throwable) {
            Log::error('WhatsApp Webhook Exception', [
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            // CRITICAL: Return 200 even on error to prevent Fonnte retry spam
            // The error has been logged for manual investigation
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
            ], 200);
        }
    }
}
