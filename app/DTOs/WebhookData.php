<?php

namespace App\DTOs;

class WebhookData
{
    public function __construct(
        public readonly ?string $sender,
        public readonly ?string $message,
        public readonly ?string $devicePhone,
        public readonly ?string $inboxId,
        public readonly ?string $timestamp,
        public readonly ?string $messageId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sender: $data['sender'] ?? null,
            message: $data['message'] ?? null,
            devicePhone: $data['device'] ?? null,
            inboxId: $data['inboxid'] ?? null,
            timestamp: $data['timestamp'] ?? null,
            messageId: $data['message_id'] ?? null,
        );
    }

    public function getNormalizedMessage(): string
    {
        return strtolower(trim($this->message ?? '', " \t\n\r\0\x0B'\""));
    }

    public function getCanonicalSender(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->sender ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }

    public function getDedupKey(): string
    {
        $canonicalSender = $this->getCanonicalSender();
        $normalizedMessage = $this->getNormalizedMessage();

        if ($this->inboxId) {
            return 'processed_msgid_'.$this->inboxId;
        }

        return 'processed_'.md5($canonicalSender.'|'.$normalizedMessage);
    }
}
