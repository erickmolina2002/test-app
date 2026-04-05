<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Jobs\RegisterProposalJob;
use App\Jobs\SendProposalNotificationJob;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProposalControllerTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validPayload = [
            'cpf' => '52998224725',
            'nome' => 'Fulano de Tal',
            'data_nascimento' => '1990-05-15',
            'valor_emprestimo' => 1000.00,
            'chave_pix' => 'teste@teste.com',
        ];
    }

    public function test_should_create_proposal_successfully(): void
    {
        Queue::fake();

        $response = $this->postJson('/proposal', $this->validPayload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'cpf',
                    'nome',
                    'data_nascimento',
                    'valor_emprestimo',
                    'chave_pix',
                    'status',
                ],
            ])
            ->assertJsonFragment([
                'cpf' => '52998224725',
                'nome' => 'Fulano de Tal',
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('proposals', [
            'cpf' => '52998224725',
            'nome' => 'Fulano de Tal',
            'status' => ProposalStatus::Pending->value,
        ]);

        Queue::assertPushed(RegisterProposalJob::class);
    }

    public function test_should_fail_validation_without_cpf(): void
    {
        $payload = $this->validPayload;
        unset($payload['cpf']);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_should_fail_validation_without_nome(): void
    {
        $payload = $this->validPayload;
        unset($payload['nome']);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_should_fail_validation_without_data_nascimento(): void
    {
        $payload = $this->validPayload;
        unset($payload['data_nascimento']);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data_nascimento']);
    }

    public function test_should_fail_validation_without_valor_emprestimo(): void
    {
        $payload = $this->validPayload;
        unset($payload['valor_emprestimo']);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valor_emprestimo']);
    }

    public function test_should_fail_validation_without_chave_pix(): void
    {
        $payload = $this->validPayload;
        unset($payload['chave_pix']);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chave_pix']);
    }

    public function test_should_fail_validation_with_future_birth_date(): void
    {
        $payload = $this->validPayload;
        $payload['data_nascimento'] = '2030-01-01';

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data_nascimento']);
    }

    public function test_should_fail_validation_with_negative_loan_value(): void
    {
        $payload = $this->validPayload;
        $payload['valor_emprestimo'] = -100;

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valor_emprestimo']);
    }

    // public function test_should_fail_validation_with_invalid_cpf(): void
    // {
    //     $payload = $this->validPayload;
    //     $payload['cpf'] = '11111111111';
    //     $response = $this->postJson('/proposal', $payload);
    //     $response->assertStatus(422)->assertJsonValidationErrors(['cpf']);
    // }

    // public function test_should_accept_cpf_with_mask_and_store_raw(): void
    // {
    //     Queue::fake();
    //     $payload = $this->validPayload;
    //     $payload['cpf'] = '529.982.247-25';
    //     $response = $this->postJson('/proposal', $payload);
    //     $response->assertStatus(201)->assertJsonFragment(['cpf' => '52998224725']);
    //     $this->assertDatabaseHas('proposals', ['cpf' => '52998224725']);
    // }

    public function test_should_return_success_message_in_portuguese(): void
    {
        Queue::fake();

        $response = $this->postJson('/proposal', $this->validPayload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Proposta cadastrada com sucesso. O registro será processado em breve.',
            ]);
    }

    public function test_should_return_validation_messages_in_portuguese(): void
    {
        $response = $this->postJson('/proposal', []);

        $response->assertStatus(422)
            ->assertJsonFragment(['O CPF é obrigatório.'])
            ->assertJsonFragment(['O nome é obrigatório.'])
            ->assertJsonFragment(['A data de nascimento é obrigatória.'])
            ->assertJsonFragment(['O valor do empréstimo é obrigatório.'])
            ->assertJsonFragment(['A chave PIX é obrigatória.']);
    }

    public function test_should_fail_validation_with_zero_loan_value(): void
    {
        Queue::fake();

        $payload = $this->validPayload;
        $payload['valor_emprestimo'] = 0;

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valor_emprestimo']);
    }

    // public function test_should_fail_validation_with_short_cpf(): void
    // {
    //     $payload = $this->validPayload;
    //     $payload['cpf'] = '1234567890';
    //     $response = $this->postJson('/proposal', $payload);
    //     $response->assertStatus(422)->assertJsonValidationErrors(['cpf']);
    // }

    // public function test_should_fail_validation_with_non_numeric_cpf(): void
    // {
    //     $payload = $this->validPayload;
    //     $payload['cpf'] = 'abcdefghijk';
    //     $response = $this->postJson('/proposal', $payload);
    //     $response->assertStatus(422)->assertJsonValidationErrors(['cpf']);
    // }

    public function test_should_fail_validation_with_invalid_date_format(): void
    {
        Queue::fake();

        $payload = $this->validPayload;
        $payload['data_nascimento'] = 'not-a-date';

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data_nascimento']);
    }

    public function test_should_fail_validation_with_non_numeric_loan_value(): void
    {
        $payload = $this->validPayload;
        $payload['valor_emprestimo'] = 'abc';

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['valor_emprestimo']);
    }

    public function test_should_fail_validation_with_nome_exceeding_max_length(): void
    {
        $payload = $this->validPayload;
        $payload['nome'] = str_repeat('a', 256);

        $response = $this->postJson('/proposal', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_full_lifecycle_pending_to_completed(): void
    {
        Http::fake([
            'util.devi.tools/api/v2/authorize' => Http::response(['status' => 'success'], 200),
            'util.devi.tools/api/v1/notify' => Http::response(['status' => 'success'], 200),
        ]);

        $response = $this->postJson('/proposal', $this->validPayload);

        $response->assertStatus(201);

        $proposalId = $response->json('data.id');

        $this->assertDatabaseHas('proposals', [
            'id' => $proposalId,
            'status' => ProposalStatus::Completed->value,
        ]);
    }

    public function test_should_set_status_failed_when_registration_fails(): void
    {
        Http::fake([
            'util.devi.tools/api/v2/authorize' => Http::response(['status' => 'fail'], 403),
        ]);

        Queue::fake();

        $response = $this->postJson('/proposal', $this->validPayload);
        $response->assertStatus(201);

        $proposalId = $response->json('data.id');

        $job = new RegisterProposalJob($proposalId);
        $job->failed(new \RuntimeException('Serviço indisponível'));

        $this->assertDatabaseHas('proposals', [
            'id' => $proposalId,
            'status' => ProposalStatus::Failed->value,
        ]);
    }

    public function test_should_set_status_failed_when_notification_fails(): void
    {
        Queue::fake();

        $response = $this->postJson('/proposal', $this->validPayload);
        $response->assertStatus(201);

        $proposalId = $response->json('data.id');

        Proposal::where('id', $proposalId)->update(['status' => ProposalStatus::Registered]);

        $job = new SendProposalNotificationJob($proposalId);
        $job->failed(new \RuntimeException('Serviço indisponível'));

        $this->assertDatabaseHas('proposals', [
            'id' => $proposalId,
            'status' => ProposalStatus::Failed->value,
        ]);
    }

    public function test_should_handle_proposal_not_found_in_register_job(): void
    {
        Queue::fake();

        $gateway = \Mockery::mock(\App\Contracts\ProposalRegistrationGatewayInterface::class);
        $gateway->shouldNotReceive('register');

        $job = new RegisterProposalJob(99999);
        $job->handle($gateway);

        $this->assertTrue(true);
    }
}
