<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackNotificationService
{
    private readonly string $webhookUrl;

    private readonly ?string $channel;

    private readonly ?string $username;

    private readonly ?string $icon;

    public function __construct()
    {
        $this->webhookUrl = config('services.slack.webhook_url') ?? env('SLACK_WEBHOOK_URL');
        $this->channel = config('services.slack.channel') ?? env('SLACK_CHANNEL');
        $this->username = config('services.slack.username') ?? env('SLACK_USERNAME', 'Geprek Bot');
        $this->icon = config('services.slack.icon') ?? env('SLACK_ICON', ':robot_face:');
    }

    /**
     * Send message to Slack webhook
     *
     * @param  string  $message  Message to send
     * @param  array  $attachments  Optional attachments
     * @param  string|null  $channel  Override default channel
     */
    public function send(string $message, array $attachments = [], ?string $channel = null): bool
    {
        if ($this->webhookUrl === '' || $this->webhookUrl === '0') {
            Log::warning('Slack webhook URL not configured');

            return false;
        }

        $payload = [
            'text' => $message,
            'username' => $this->username,
            'icon_emoji' => $this->icon,
        ];

        if ($channel) {
            $payload['channel'] = $channel;
        } elseif ($this->channel) {
            $payload['channel'] = $this->channel;
        }

        if ($attachments !== []) {
            $payload['attachments'] = $attachments;
        }

        try {
            $response = Http::post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Slack notification sent', ['message' => $message]);

                return true;
            }

            Log::error('Failed to send Slack notification', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $exception) {
            Log::error('Exception sending Slack notification', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send success notification
     *
     * @param  string  $title  Title of the notification
     * @param  string  $message  Detail message
     * @param  array  $data  Additional data to include
     */
    public function success(string $title, string $message, array $data = []): bool
    {
        $fields = $this->formatFields($data);

        return $this->send(sprintf(':white_check_mark: *%s*', $title), [[
            'color' => 'good',
            'title' => $title,
            'text' => $message,
            'fields' => $fields,
            'footer' => config('app.name'),
            'ts' => now()->timestamp,
        ]]);
    }

    /**
     * Send warning notification
     *
     * @param  string  $title  Title of the notification
     * @param  string  $message  Detail message
     * @param  array  $data  Additional data to include
     */
    public function warning(string $title, string $message, array $data = []): bool
    {
        $fields = $this->formatFields($data);

        return $this->send(sprintf(':warning: *%s*', $title), [[
            'color' => 'warning',
            'title' => $title,
            'text' => $message,
            'fields' => $fields,
            'footer' => config('app.name'),
            'ts' => now()->timestamp,
        ]]);
    }

    /**
     * Send error notification
     *
     * @param  string  $title  Title of the notification
     * @param  string  $message  Detail message
     * @param  array  $data  Additional data to include
     */
    public function error(string $title, string $message, array $data = []): bool
    {
        $fields = $this->formatFields($data);

        return $this->send(sprintf(':x: *%s*', $title), [[
            'color' => 'danger',
            'title' => $title,
            'text' => $message,
            'fields' => $fields,
            'footer' => config('app.name'),
            'ts' => now()->timestamp,
        ]]);
    }

    /**
     * Send info notification
     *
     * @param  string  $title  Title of the notification
     * @param  string  $message  Detail message
     * @param  array  $data  Additional data to include
     */
    public function info(string $title, string $message, array $data = []): bool
    {
        $fields = $this->formatFields($data);

        return $this->send(sprintf(':information_source: *%s*', $title), [[
            'color' => '#439FE0',
            'title' => $title,
            'text' => $message,
            'fields' => $fields,
            'footer' => config('app.name'),
            'ts' => now()->timestamp,
        ]]);
    }

    /**
     * Send new order notification
     *
     * @param  array  $orderData  Order data
     */
    public function newOrder(array $orderData): bool
    {
        $fields = [
            ['title' => 'Customer', 'value' => $orderData['customer_name'] ?? 'Unknown', 'short' => true],
            ['title' => 'Phone', 'value' => $orderData['customer_phone'] ?? 'Unknown', 'short' => true],
            ['title' => 'Total', 'value' => 'Rp '.number_format($orderData['total'] ?? 0, 0, ',', '.'), 'short' => true],
            ['title' => 'Items', 'value' => $orderData['items_count'] ?? 0 .' items', 'short' => true],
        ];

        if (! empty($orderData['delivery_method'])) {
            $fields[] = ['title' => 'Delivery', 'value' => ucfirst((string) $orderData['delivery_method']), 'short' => true];
        }

        if (! empty($orderData['payment_method'])) {
            $fields[] = ['title' => 'Payment', 'value' => ucfirst((string) $orderData['payment_method']), 'short' => true];
        }

        return $this->send(':shopping_cart: *New Order Received*', [[
            'color' => '#36a64f',
            'title' => 'New Order #'.($orderData['order_id'] ?? 'Pending'),
            'fields' => $fields,
            'footer' => config('app.name'),
            'ts' => now()->timestamp,
        ]]);
    }

    /**
     * Send webhook error notification
     *
     * @param  array  $errorData  Error data from webhook
     */
    public function webhookError(array $errorData): bool
    {
        $fields = [
            ['title' => 'Sender', 'value' => $errorData['sender'] ?? 'Unknown', 'short' => true],
            ['title' => 'Device', 'value' => $errorData['device'] ?? 'Unknown', 'short' => true],
            ['title' => 'Error', 'value' => $errorData['error'] ?? 'Unknown error', 'short' => false],
        ];

        if (! empty($errorData['message'])) {
            $fields[] = ['title' => 'Message', 'value' => substr((string) $errorData['message'], 0, 200), 'short' => false];
        }

        if (! empty($errorData['trace'])) {
            $fields[] = ['title' => 'Trace', 'value' => substr((string) $errorData['trace'], 0, 500), 'short' => false];
        }

        return $this->error('Webhook Error', 'An error occurred while processing WhatsApp webhook', $fields);
    }

    /**
     * Format data into Slack fields
     *
     * @param  array  $data  Data to format
     */
    private function formatFields(array $data): array
    {
        $fields = [];

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            } elseif (! is_string($value)) {
                $value = (string) $value;
            }

            // Truncate long values
            if (strlen($value) > 100) {
                $value = substr($value, 0, 97).'...';
            }

            $fields[] = [
                'title' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
                'short' => strlen($value) < 30,
            ];
        }

        return $fields;
    }

    /**
     * Check if Slack is configured
     */
    public function isConfigured(): bool
    {
        return $this->webhookUrl !== '' && $this->webhookUrl !== '0';
    }
}
