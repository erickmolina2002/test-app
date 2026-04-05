<?php

namespace App\Contracts;

interface ProposalRegistrationGatewayInterface
{
    public function register(array $proposalData): bool;
}
