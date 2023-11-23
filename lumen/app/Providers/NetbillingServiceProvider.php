<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use ProBillerNG\Netbilling\Domain\Services\ApiClient;
use ProBillerNG\Netbilling\Domain\Services\MembershipApiRequest;
use ProBillerNG\Netbilling\Domain\Services\TransactionGenerator;
use ProBillerNG\Netbilling\Infrastructure\Domain\NetbillingApiRequest;
use ProBillerNG\Netbilling\Infrastructure\Domain\NetbillingMembershipApiRequest;
use ProBillerNG\Netbilling\Infrastructure\Domain\NetbillingTransactionGenerator;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingExistingCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\NetbillingUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;
use ProBillerNG\Transaction\Infrastructure\Application\Services\LaravelNetbillingUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\BaseNetbillingCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\CircuitBreakerNetbillingCancelRebillAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\CircuitBreakerNetbillingUpdateRebillAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardTranslationService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillExistingCardTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillNewCardTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\UpdateRebillNetbillingAdapter;

class NetbillingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app
            ->when(PerformNetbillingNewCreditCardSaleCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(NetbillingChargeService::class);

        $this->app
            ->when(PerformNetbillingExistingCreditCardSaleCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(NetbillingChargeService::class);

        $this->app
            ->when(PerformNetbillingCancelRebillCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(NetbillingChargeService::class);

        $this->app
            ->when(PerformNetbillingUpdateRebillCommandHandler::class)
            ->needs(UpdateRebillService::class)
            ->give(NetbillingUpdateRebillTranslatingService::class);

        $this->app->bind(
            NetbillingUpdateRebillCommandHandlerFactory::class,
            LaravelNetbillingUpdateRebillCommandHandlerFactory::class
        );

        $this->app->bind(
            ApiClient::class,
            NetbillingApiRequest::class
        );

        $this->app->bind(
            TransactionGenerator::class,
            NetbillingTransactionGenerator::class
        );

        $this->app->bind(
            MembershipApiRequest::class,
            NetbillingMembershipApiRequest::class
        );

        $this->app
            ->when(NetbillingUpdateRebillNewCardTranslator::class)
            ->needs(UpdateRebillNetbillingAdapter::class)
            ->give(CircuitBreakerNetbillingUpdateRebillAdapterInterface::class);

        $this->app
            ->when(NetbillingUpdateRebillExistingCardTranslator::class)
            ->needs(UpdateRebillNetbillingAdapter::class)
            ->give(CircuitBreakerNetbillingUpdateRebillAdapterInterface::class);

        $this->app
            ->when(NetbillingCreditCardTranslationService::class)
            ->needs(BaseNetbillingCancelRebillAdapter::class)
            ->give(CircuitBreakerNetbillingCancelRebillAdapterInterface::class);
    }
}
