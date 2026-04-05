<?php

namespace App\Jobs;

use App\Contracts\ProposalRegistrationGatewayInterface;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisterProposalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public readonly int $proposalId,
    ) {}

    public function handle(ProposalRegistrationGatewayInterface $gateway): void
    {
        Log::info('Iniciando registro da proposta.', [
            'proposal_id' => $this->proposalId,
            'attempt' => $this->attempts(),
        ]);

        $proposal = Proposal::find($this->proposalId);

        if (! $proposal) {
            Log::warning('Proposta não encontrada. Ignorando job.', ['proposal_id' => $this->proposalId]);
            return;
        }

        $gateway->register($proposal->toArray());

        $proposal->update(['status' => ProposalStatus::Registered]);

        Log::info('Proposta registrada com sucesso.', ['proposal_id' => $this->proposalId]);

        SendProposalNotificationJob::dispatch($this->proposalId);

        Log::info('Job de notificação despachado para a fila.', ['proposal_id' => $this->proposalId]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Registro da proposta falhou após todas as tentativas.', [
            'proposal_id' => $this->proposalId,
            'error' => $exception->getMessage(),
        ]);

        Proposal::where('id', $this->proposalId)
            ->update(['status' => ProposalStatus::Failed]);
    }
}
