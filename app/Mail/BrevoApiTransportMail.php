<?php

// app/Mail/BrevoApiTransport.php
namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Http;

class BrevoApiTransportMail extends AbstractTransport
{
    public function __construct(protected string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (!$email instanceof Email) {
            throw new \RuntimeException('Unsupported message type for Brevo transport.');
        }

        $payload = [
            'sender' => [
                'email' => $email->getFrom()[0]->getAddress(),
                'name'  => $email->getFrom()[0]->getName() ?: null,
            ],
            'to' => array_map(fn($addr) => [
                'email' => $addr->getAddress(),
                'name'  => $addr->getName() ?: null,
            ], $email->getTo()),
            'subject' => $email->getSubject(),
        ];

        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        }
        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }

        $response = Http::withHeaders([
                                          'api-key' => $this->apiKey,
                                          'content-type' => 'application/json',
                                          'accept' => 'application/json',
                                      ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Brevo API send failed: ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }
}
