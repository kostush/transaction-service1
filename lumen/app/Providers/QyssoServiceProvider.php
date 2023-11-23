<?php

namespace App\Providers;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use ProBillerNG\Epoch\Application\Services\NewSaleCommandHandler;
use ProBillerNG\Epoch\Domain\Services\NewSaleService;
use ProBillerNG\Epoch\Infrastructure\Domain\Services\DynamicPricing;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoNewSaleHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoRebillPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoRebillPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnQyssoCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnQyssoCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformQyssoNewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Services\QyssoNewSaleAdapter as QyssoNewSaleAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\QyssoService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\QyssoNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\QyssoTranslatingService;

class QyssoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, \GuzzleHttp\Client::class);
        $this->app->bind(QyssoService::class, QyssoTranslatingService::class);

        $this->app
            ->when(AddBillerInteractionForJoinOnQyssoCommandHandler::class)
            ->needs(QyssoJoinPostbackTransactionDTOAssembler::class)
            ->give(QyssoJoinPostbackHttpCommandDTOAssembler::class);

        $this->app
            ->when(AddBillerInteractionForRebillOnQyssoCommandHandler::class)
            ->needs(QyssoRebillPostbackTransactionDTOAssembler::class)
            ->give(QyssoRebillPostbackHttpCommandDTOAssembler::class);

        $this->app
            ->when(NewSaleCommandHandler::class)
            ->needs(NewSaleService::class)
            ->give(DynamicPricing::class);

        $this->app
            ->when(PerformQyssoNewSaleCommandHandler::class)
            ->needs(QyssoNewSaleTransactionDTOAssembler::class)
            ->give(QyssoNewSaleHttpCommandDTOAssembler::class);

        $this->app
            ->when(QyssoTranslatingService::class)
            ->needs(QyssoNewSaleAdapterInterface::class)
            ->give(QyssoNewSaleAdapter::class);
    }
}
