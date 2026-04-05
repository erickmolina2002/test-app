<?php

namespace App\Services;

use App\Contracts\ProposalRegistrationGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ExternalProposalRegistrationGateway implements ProposalRegistrationGatewayInterface
{
    private const AUTHORIZE_URL = 'https://util.devi.tools/api/v2/authorize';

    public function register(array $proposalData): bool
    {
        Log::info('Chamando API de registro.', ['url' => self::AUTHORIZE_URL]);

        $response = Http::timeout(10)->get(self::AUTHORIZE_URL);

        Log::info('Resposta da API de registro.', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Serviço de registro indisponível. Status: {$response->status()}"
            );
        }

        $body = $response->json();

        if (($body['status'] ?? '') !== 'success') {
            throw new RuntimeException('Registro de proposta não autorizado.');
        }

        return true;
    }
}
