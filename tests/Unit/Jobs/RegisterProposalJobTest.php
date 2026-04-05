<?php

namespace Tests\Unit\Jobs;

use App\Contracts\ProposalRegistrationGatewayInterface;
use App\Enums\ProposalStatus;
use App\Jobs\RegisterProposalJob;
use App\Jobs\SendProposalNotificationJob;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class RegisterProposalJobTest extends TestCase
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
            'status' => ProposalStatus::Pending,
        ]);
    }

    public function test_should_register_proposal_and_dispatch_notification(): void
    {
        Queue::fake();

        $proposal = $this->createProposal();

        $gateway = Mockery::mock(ProposalRegistrationGatewayInterface::class);
        $gateway->shouldReceive('register')->once()->andReturn(true);

        $job = new RegisterProposalJob($proposal->id);
        $job->handle($gateway);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => ProposalStatus::Registered->value,
        ]);

        Queue::assertPushed(SendProposalNotificationJob::class, function ($job) use ($proposal) {
            return $job->proposalId === $proposal->id;
        });
    }

    public function test_should_throw_exception_when_gateway_fails(): void
    {
        $proposal = $this->createProposal();

        $gateway = Mockery::mock(ProposalRegistrationGatewayInterface::class);
        $gateway->shouldReceive('register')
            ->once()
            ->andThrow(new RuntimeException('Serviço indisponível'));

        $this->expectException(RuntimeException::class);

        $job = new RegisterProposalJob($proposal->id);
        $job->handle($gateway);
    }
}
