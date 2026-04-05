<?php

namespace App\Jobs;

use App\Contracts\NotificationGatewayInterface;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendProposalNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public readonly int $proposalId,
    ) {}

    public function handle(NotificationGatewayInterface $gateway): void
    {
        Log::info('Iniciando envio de notificação.', [
            'proposal_id' => $this->proposalId,
            'attempt' => $this->attempts(),
        ]);

        $proposal = Proposal::find($this->proposalId);

        if (! $proposal) {
            Log::warning('Proposta não encontrada. Ignorando job.', ['proposal_id' => $this->proposalId]);
            return;
        }

        $gateway->send($proposal->toArray());

        $proposal->update(['status' => ProposalStatus::Completed]);

        Log::info('Notificação enviada com sucesso. Proposta concluída.', ['proposal_id' => $this->proposalId]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Envio de notificação falhou após todas as tentativas.', [
            'proposal_id' => $this->proposalId,
            'error' => $exception->getMessage(),
        ]);

        Proposal::where('id', $this->proposalId)
            ->update(['status' => ProposalStatus::Failed]);
    }
}
