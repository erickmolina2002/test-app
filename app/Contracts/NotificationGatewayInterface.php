<?php

namespace App\Contracts;

interface NotificationGatewayInterface
{
    public function send(array $proposalData): bool;
}
