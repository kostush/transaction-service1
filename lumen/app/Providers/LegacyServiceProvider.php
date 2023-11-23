<?php
declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\ServiceProvider;
use Odesk\Phystrix\CommandFactory;
use ProbillerNG\LegacyServiceClient\Api\DefaultApi;
use ProbillerNG\LegacyServiceClient\Configuration as LegacyServiceConfiguration;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\PerformLegacyNewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Domain\Services\LegacyPostbackResponseService;
use ProBillerNG\Transaction\Domain\Services\LegacyService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\CircuitBreakerLegacyNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyGeneratePurchaseUrlAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleTranslatingService;
use Laravel\Lumen\Application;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\AddBillerInteractionForJoinOnLegacyCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyPostbackResponseTranslatingService;

/**
 * Class LegacyServiceProvider
 * @package App\Providers
 */
class LegacyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //Add Biller interaction
        //DTO
        $this->app
            ->when(AddBillerInteractionForJoinOnLegacyCommandHandler::class)
            ->needs(LegacyJoinPostbackTransactionDTOAssembler::class)
            ->give(LegacyJoinPostbackHttpCommandDTOAssembler::class);

        //Domain-Infrastructure interface
        $this->app->bind(LegacyPostbackResponseService::class, LegacyPostbackResponseTranslatingService::class);

        //New sale
        //Domain-Infrastructure interface
        $this->app->bind(LegacyService::class, LegacyNewSaleTranslatingService::class);

        //DTO
        $this->app
            ->when(PerformLegacyNewSaleCommandHandler::class)
            ->needs(LegacyNewSaleTransactionDTOAssembler::class)
            ->give(LegacyNewSaleHttpCommandDTOAssembler::class);

        //Circuit Breaker and Anti-corruption layer
        $this->app->bind(
            LegacyNewSaleAdapter::class,
            function () {
                return new CircuitBreakerLegacyNewSaleAdapter(
                    $this->app->make(CommandFactory::class),
                    $this->app->make(LegacyGeneratePurchaseUrlAdapter::class)
                );
            }
        );

        $this->app->bind(
            LegacyNewSaleClient::class,
            function (Application $application) {
                return new LegacyNewSaleClient(
                    new DefaultApi(
                        new Client(
                            [
                                RequestOptions::CONNECT_TIMEOUT => $application['config']
                                    ->get('clientapis.legacyService.connectionTimeout'),
                                RequestOptions::TIMEOUT         => $application['config']
                                    ->get('clientapis.legacyService.timeout'),
                            ]
                        ),
                        (new LegacyServiceConfiguration())
                            ->setAccessToken($application['config']->get('clientapis.legacyService.accessToken'))
                            ->setUsername($application['config']->get('clientapis.legacyService.username'))
                            ->setPassword($application['config']->get('clientapis.legacyService.password'))
                            ->setHost($application['config']->get('clientapis.legacyService.host'))
                            ->setDebug($application['config']->get('clientapis.legacyService.debug'))
                            ->setDebugFile(storage_path('logs/' . $application['config']->get('clientapis.legacyService.debugFile')))
                            ->setTempFolderPath(storage_path('logs/' . $application['config']->get('clientapis.legacyService.tempFolderPath')))
                    )
                );
            }
        );
    }
}
