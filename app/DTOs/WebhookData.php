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
        // NEW FIELDS from Fonnte Update 12 Jan 2026
        public readonly ?string $name,          // Sender name
        public readonly ?string $text,          // Button text
        public readonly ?string $member,        // Group member who sent
        public readonly ?string $location,      // Location data
        public readonly ?string $pollname,      // Poll name
        public readonly string|array|null $choices = null,       // Poll choices (can be array or string)
        public readonly ?string $url,           // Media URL (feature package)
        public readonly ?string $filename,      // Filename (feature package)
        public readonly ?string $extension,     // File extension (feature package)
        public readonly ?string $type,          // Message type
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
            // NEW FIELDS
            name: $data['name'] ?? null,
            text: $data['text'] ?? null,
            member: $data['member'] ?? null,
            location: $data['location'] ?? null,
            pollname: $data['pollname'] ?? null,
            choices: $data['choices'] ?? null,
            url: $data['url'] ?? null,
            filename: $data['filename'] ?? null,
            extension: $data['extension'] ?? null,
            type: $data['type'] ?? 'text',
        );
    }

    public function getNormalizedMessage(): string
    {
        return strtolower(trim($this->message ?? '', " \t\n\r\0\x0B'\""));
    }

    public function getCanonicalSender(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->sender ?? '');
        if (str_starts_with((string) $phone, '0')) {
            return '62'.substr((string) $phone, 1);
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
