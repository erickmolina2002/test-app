<?php

namespace Tests\Unit\UseCases;

use App\DTOs\CreateProposalDTO;
use App\Enums\ProposalStatus;
use App\Jobs\RegisterProposalJob;
use App\UseCases\CreateProposalUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateProposalUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_create_proposal_and_dispatch_job(): void
    {
        Queue::fake();

        $useCase = new CreateProposalUseCase();

        $dto = CreateProposalDTO::from([
            'cpf' => '52998224725',
            'nome' => 'Fulano de Tal',
            'data_nascimento' => '1990-05-15',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'teste@teste.com',
        ]);

        $result = $useCase->execute($dto);

        $this->assertEquals('52998224725', $result->cpf);
        $this->assertEquals('Fulano de Tal', $result->nome);
        $this->assertEquals(ProposalStatus::Pending, $result->status);

        $this->assertDatabaseHas('proposals', [
            'cpf' => '52998224725',
            'nome' => 'Fulano de Tal',
        ]);

        Queue::assertPushed(RegisterProposalJob::class, function ($job) use ($result) {
            return $job->proposalId === $result->id;
        });
    }
}
