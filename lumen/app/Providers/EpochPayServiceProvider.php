<?php

namespace App\Providers;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Odesk\Phystrix\CommandFactory;
use ProBillerNG\Epoch\Application\Services\NewSaleCommandHandler;
use ProBillerNG\Epoch\Application\Services\PostbackTranslateCommandHandler;
use ProBillerNG\Epoch\Domain\Services\NewSaleService;
use ProBillerNG\Epoch\Domain\Services\PostbackService;
use ProBillerNG\Epoch\Domain\Services\PostbackTranslateService;
use ProBillerNG\Epoch\Infrastructure\Domain\Services\DynamicPricing;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochNewSaleHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnEpochCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformEpochNewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Services\EpochNewSaleAdapter as EpochNewSaleAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\EpochPostbackAdapter as EpochPostbackAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\EpochService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerEpochNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochPostbackAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochTranslatingService;

class EpochPayServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ClientInterface::class, \GuzzleHttp\Client::class);
        $this->app->bind(EpochService::class, EpochTranslatingService::class);

        $this->app
            ->when(PostbackTranslateCommandHandler::class)
            ->needs(PostbackService::class)
            ->give(PostbackTranslateService::class);

        // DTO - Add Biller Interaction for join
        $this->app
            ->when(AddBillerInteractionForJoinOnEpochCommandHandler::class)
            ->needs(EpochJoinPostbackTransactionDTOAssembler::class)
            ->give(EpochJoinPostbackHttpCommandDTOAssembler::class);


        $this->app
            ->when(EpochTranslatingService::class)
            ->needs(EpochPostbackAdapterInterface::class)
            ->give(EpochPostbackAdapter::class);


        // DTO - New Sale
        $this->app
            ->when(NewSaleCommandHandler::class)
            ->needs(NewSaleService::class)
            ->give(DynamicPricing::class);

        $this->app
            ->when(PerformEpochNewSaleCommandHandler::class)
            ->needs(EpochNewSaleTransactionDTOAssembler::class)
            ->give(EpochNewSaleHttpCommandDTOAssembler::class);

        $this->app->bind(
            EpochNewSaleAdapterInterface::class,
            function () {
                return new CircuitBreakerEpochNewSaleAdapter(
                    $this->app->make(CommandFactory::class),
                    $this->app->make(EpochNewSaleAdapter::class)
                );
            }
        );
    }
}
