<?php

namespace App\Providers;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use ProBillerNG\Pumapay\Application\Services\PostbackCommandHandler;
use ProBillerNG\Pumapay\Domain\Services\PerformRequestService;
use ProBillerNG\Pumapay\Domain\Services\PostbackService;
use ProBillerNG\Pumapay\Domain\Services\PostbackTranslateService;
use ProBillerNG\Pumapay\Infrastructure\Domain\Services\PerformRequestService as InfrastructurePerformRequestService;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayCancelRebillDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayCancelRebillHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommandHandler;
use ProBillerNG\Transaction\Domain\Services\PumapayPostbackAdapter as PumapayPostbackAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\PumapayQrCodeTransactionService;
use ProBillerNG\Transaction\Domain\Services\PumapayRetrieveQrCodeAdapter as PumapayQrCodeAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\PumapayService;
use ProBillerNG\Transaction\Domain\Services\PumapayTransactionService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayPostbackAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayRetrieveQrCodeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslatingService;

class PumaPayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, \GuzzleHttp\Client::class);
        $this->app->bind(PerformRequestService::class, InfrastructurePerformRequestService::class);
        $this->app->bind(PumapayService::class, PumapayTranslatingService::class);

        $this->app
            ->when(PostbackCommandHandler::class)
            ->needs(PostbackService::class)
            ->give(PostbackTranslateService::class);

        // DTO
        $this->app
            ->when(AddBillerInteractionForJoinOnPumapayCommandHandler::class)
            ->needs(PumapayJoinPostbackTransactionDTOAssembler::class)
            ->give(PumapayJoinPostbackHttpCommandDTOAssembler::class);

        $this->app
            ->when(AddBillerInteractionForRebillOnPumapayCommandHandler::class)
            ->needs(PumapayRebillPostbackTransactionDTOAssembler::class)
            ->give(PumapayRebillPostbackHttpCommandDTOAssembler::class);

        $this->app
            ->when(PumapayCancelRebillCommandHandler::class)
            ->needs(PumapayCancelRebillDTOAssembler::class)
            ->give(PumapayCancelRebillHttpCommandDTOAssembler::class);

        $this->app
            ->when(PumapayTranslatingService::class)
            ->needs(PumapayQrCodeAdapterInterface::class)
            ->give(PumapayRetrieveQrCodeAdapter::class);

        $this->app
            ->when(PumapayTranslatingService::class)
            ->needs(PumapayPostbackAdapterInterface::class)
            ->give(PumapayPostbackAdapter::class);

        $this->app
            ->when(RetrievePumapayQrCodeCommandHandler::class)
            ->needs(PumapayTransactionService::class)
            ->give(PumapayQrCodeTransactionService::class);
    }
}
