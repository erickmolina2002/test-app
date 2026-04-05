<?php

namespace App\Providers;

use App\Contracts\NotificationGatewayInterface;
use App\Contracts\ProposalRegistrationGatewayInterface;
use App\Services\ExternalNotificationGateway;
use App\Services\ExternalProposalRegistrationGateway;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProposalRegistrationGatewayInterface::class,
            ExternalProposalRegistrationGateway::class,
        );

        $this->app->bind(
            NotificationGatewayInterface::class,
            ExternalNotificationGateway::class,
        );
    }

    public function boot(): void
    {
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'proposal');
        });
    }
}
