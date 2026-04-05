<?php

namespace Tests\Unit\Jobs;

use App\Contracts\NotificationGatewayInterface;
use App\Enums\ProposalStatus;
use App\Jobs\SendProposalNotificationJob;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SendProposalNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    private function createProposal(): Proposal
    {
        return Proposal::create([
            'cpf' => '52998224725',
            'nome' => 'Fulano de Tal',
            'data_nascimento' => '1990-05-15',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'teste@teste.com',
            'status' => ProposalStatus::Registered,
        ]);
    }

    public function test_should_send_notification_and_update_status(): void
    {
        $proposal = $this->createProposal();

        $gateway = Mockery::mock(NotificationGatewayInterface::class);
        $gateway->shouldReceive('send')->once()->andReturn(true);

        $job = new SendProposalNotificationJob($proposal->id);
        $job->handle($gateway);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => ProposalStatus::Completed->value,
        ]);
    }

    public function test_should_throw_exception_when_notification_fails(): void
    {
        $proposal = $this->createProposal();

        $gateway = Mockery::mock(NotificationGatewayInterface::class);
        $gateway->shouldReceive('send')
            ->once()
            ->andThrow(new RuntimeException('Serviço indisponível'));

        $this->expectException(RuntimeException::class);

        $job = new SendProposalNotificationJob($proposal->id);
        $job->handle($gateway);
    }
}
