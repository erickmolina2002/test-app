<?php

namespace App\Services;

use App\Contracts\NotificationGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExternalNotificationGateway implements NotificationGatewayInterface
{
    private const NOTIFY_URL = 'https://util.devi.tools/api/v1/notify';

    public function send(array $proposalData): bool
    {
        Log::info('Chamando API de notificação.', ['url' => self::NOTIFY_URL]);

        $response = Http::timeout(10)->post(self::NOTIFY_URL, $proposalData);

        Log::info('Resposta da API de notificação.', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->status() >= 400) {
            throw new RuntimeException(
                "Serviço de notificação indisponível. Status: {$response->status()}"
            );
        }

        return true;
    }
}
