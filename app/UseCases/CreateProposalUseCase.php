<?php

namespace App\UseCases;

use App\DTOs\CreateProposalDTO;
use App\Enums\ProposalStatus;
use App\Jobs\RegisterProposalJob;
use App\Models\Proposal;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CreateProposalUseCase
{
    public function execute(CreateProposalDTO $dto): Proposal
    {
        $existingProposal = Proposal::where('cpf', $dto->cpf)->first();

        if ($existingProposal) {
            throw ValidationException::withMessages([
                'cpf' => ['Já existe uma proposta cadastrada para este CPF.'],
            ]);
        }

        $proposal = Proposal::create([
            'cpf' => $dto->cpf,
            'nome' => $dto->nome,
            'data_nascimento' => $dto->dataNascimento,
            'valor_emprestimo' => $dto->valorEmprestimo,
            'chave_pix' => $dto->chavePix,
            'status' => ProposalStatus::Pending,
        ]);

        Log::info('Proposta criada com sucesso.', ['proposal_id' => $proposal->id, 'cpf' => $dto->cpf]);

        RegisterProposalJob::dispatch($proposal->id);

        Log::info('Job de registro despachado para a fila.', ['proposal_id' => $proposal->id]);

        return $proposal;
    }
}
