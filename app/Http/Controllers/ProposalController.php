<?php

namespace App\Http\Controllers;

use App\DTOs\CreateProposalDTO;
use App\UseCases\CreateProposalUseCase;
use Dedoc\Scramble\Attributes\BodyParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProposalController extends Controller
{
    public function __construct(
        private readonly CreateProposalUseCase $createProposalUseCase,
    ) {}

    #[BodyParameter('cpf', example: '12345678900')]
    #[BodyParameter('nome', example: 'João da Silva')]
    #[BodyParameter('data_nascimento', example: '1990-05-15')]
    #[BodyParameter('valor_emprestimo', example: 1500.00)]
    #[BodyParameter('chave_pix', example: '12345678900')]
    public function store(CreateProposalDTO $dto): JsonResponse
    {
        $proposal = $this->createProposalUseCase->execute($dto);

        return response()->json([
            'message' => 'Proposta cadastrada com sucesso. O registro será processado em breve.',
            'data' => $proposal->toArray(),
        ], Response::HTTP_CREATED);
    }
}
