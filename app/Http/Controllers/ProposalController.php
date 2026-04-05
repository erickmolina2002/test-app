<?php

namespace App\Http\Controllers;

use App\DTOs\CreateProposalDTO;
use App\UseCases\CreateProposalUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class ProposalController extends Controller
{
    public function __construct(
        private readonly CreateProposalUseCase $createProposalUseCase,
    ) {}

    public function store(CreateProposalDTO $dto): JsonResponse
    {
        $proposal = $this->createProposalUseCase->execute($dto);

        return response()->json([
            'message' => 'Proposta cadastrada com sucesso. O registro será processado em breve.',
            'data' => $proposal->toArray(),
        ], Response::HTTP_CREATED);
    }
}
