<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Odesk\Phystrix\CommandFactory;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\StartRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\StopRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateOtherPaymentTypeSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStartRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStopRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\ChargeThreeDService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoService;
use ProBillerNG\Transaction\Domain\Services\LookupThreeDsTwoTranslatingService;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;
use ProBillerNG\Transaction\Infrastructure\Application\Services\LaravelRocketgateUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Infrastructure\Domain\CardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\ExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\NewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateChargeExistingCreditCardAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateChargeNewCreditCardAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateCompleteThreeDCreditCardAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateLookupThreeDsTwoAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateSimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateSuspendRebillAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateUpdateRebillAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerRocketgateUploadCardAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\LookupThreeDsTwoAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCompleteThreeDCreditCardAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateLookupThreeDsTwoAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateLookupThreeDsTwoTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateLookupThreeDsTwoTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateNewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\SimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\SuspendRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\UpdateRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\UpdateRebillClient;

class RocketgateServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app
            ->when(PerformRocketgateNewCreditCardSaleCommandHandler::class)
            ->needs(ChargeThreeDService::class)
            ->give(
                function (Application $app) {
                    return new ChargeThreeDService(
                        $app->make(RocketgateChargeService::class)
                    );
                }
            );

        $this->app
            ->when(LookupThreeDsTwoService::class)
            ->needs(ChargeService::class)
            ->give(RocketgateChargeService::class);

        $this->app
            ->when(PerformRocketgateExistingCreditCardSaleCommandHandler::class)
            ->needs(ChargeThreeDService::class)
            ->give(
                function (Application $app) {
                    return new ChargeThreeDService(
                        $app->make(RocketgateChargeService::class)
                    );
                }
            );

        $this->app
            ->when(PerformRocketgateOtherPaymentTypeSaleCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(RocketgateChargeService::class);

        $this->app
            ->when(PerformRocketgateCancelRebillCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(RocketgateChargeService::class);

        $this->app->bind(
            RocketgateUpdateRebillCommandHandlerFactory::class,
            LaravelRocketgateUpdateRebillCommandHandlerFactory::class
        );

        $this->app->bind(
            LookupThreeDsTwoAdapter::class,
            RocketgateLookupThreeDsTwoAdapter::class
        );

        $this->app
            ->when(PerformRocketgateStartRebillCommandHandler::class)
            ->needs(UpdateRebillService::class)
            ->give(RocketgateUpdateRebillTranslatingService::class);

        $this->app
            ->when(LookupThreeDsTwoService::class)
            ->needs(LookupThreeDsTwoTranslatingService::class)
            ->give(RocketgateLookupThreeDsTwoTranslatingService::class);

        $this->app
            ->when(PerformRocketgateUpdateRebillCommandHandler::class)
            ->needs(UpdateRebillService::class)
            ->give(RocketgateUpdateRebillTranslatingService::class);

        $this->app
            ->when(PerformRocketgateStopRebillCommandHandler::class)
            ->needs(UpdateRebillService::class)
            ->give(RocketgateUpdateRebillTranslatingService::class);

        $this->app
            ->when(PerformRocketgateCompleteThreeDCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(RocketgateChargeService::class);

        $this->app
            ->when(PerformRocketgateSimplifiedCompleteThreeDCommandHandler::class)
            ->needs(ChargeService::class)
            ->give(RocketgateChargeService::class);

        $this->app->bind(
            ExistingCreditCardChargeAdapter::class,
            function () {
                return new CircuitBreakerRocketgateChargeExistingCreditCardAdapterInterface(
                    $this->app->make(CommandFactory::class),
                    new RocketgateExistingCreditCardChargeAdapter(
                        new ChargeClient(
                            null,
                            new ChargeWithExistingCreditCardCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            NewCreditCardChargeAdapter::class,
            function () {
                return new CircuitBreakerRocketgateChargeNewCreditCardAdapterInterface(
                    $this->app->make(CommandFactory::class),
                    new RocketgateNewCreditCardChargeAdapter(
                        new ChargeClient(
                            new ChargeWithNewCreditCardCommandHandler(),
                            null
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            SuspendRebillAdapter::class,
            function () {
                return new CircuitBreakerRocketgateSuspendRebillAdapterInterface(
                    $this->app->make(CommandFactory::class),
                    new RocketgateSuspendRebillAdapter(
                        new ChargeClient(
                            null,
                            null,
                            new SuspendRebillCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            UpdateRebillAdapter::class,
            function () {
                return new CircuitBreakerRocketgateUpdateRebillAdapterInterface(
                    $this->app->make(CommandFactory::class),
                    new RocketgateUpdateRebillAdapter(
                        new UpdateRebillClient(
                            new StartRebillCommandHandler,
                            new StopRebillCommandHandler,
                            new UpdateRebillCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            CompleteThreeDAdapter::class,
            function () {
                return new CircuitBreakerRocketgateCompleteThreeDCreditCardAdapter(
                    $this->app->make(CommandFactory::class),
                    new RocketgateCompleteThreeDCreditCardAdapter(
                        new ChargeClient(
                            null,
                            null,
                            null,
                            new CompleteThreeDCreditCardCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            SimplifiedCompleteThreeDAdapter::class,
            function () {
                return new CircuitBreakerRocketgateSimplifiedCompleteThreeDAdapter(
                    $this->app->make(CommandFactory::class),
                    new RocketgateSimplifiedCompleteThreeDAdapter(
                        new ChargeClient(
                            null,
                            null,
                            null,
                            null,
                            new SimplifiedCompleteThreeDCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            LookupThreeDsTwoAdapter::class,
            function () {
                return new CircuitBreakerRocketgateLookupThreeDsTwoAdapter(
                    $this->app->make(CommandFactory::class),
                    new RocketgateLookupThreeDsTwoAdapter(
                        new ThreeDSTwoLookupCommandHandler(),
                        new RocketgateLookupThreeDsTwoTranslator()
                    )
                );
            }
        );

        $this->app->bind(
            CardUploadAdapter::class,
            function () {
                return new CircuitBreakerRocketgateUploadCardAdapter(
                    $this->app->make(CommandFactory::class),
                    new RocketgateCardUploadAdapter(
                        new ChargeClient(
                            null,
                            null,
                            null,
                            null,
                            null,
                            new CardUploadCommandHandler()
                        ),
                        new RocketgateCreditCardChargeTranslator()
                    )
                );
            }
        );
    }
}
