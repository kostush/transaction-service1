<?php

namespace App\Providers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Odesk\Phystrix\ApcStateStorage;
use Odesk\Phystrix\CircuitBreakerFactory;
use Odesk\Phystrix\CommandFactory;
use Odesk\Phystrix\CommandMetricsFactory;
use Odesk\Phystrix\RequestCache;
use Odesk\Phystrix\RequestLog;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Transaction\Application\DTO\AbortHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\AbortTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\HttpQueryHealthDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionHealthDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionHealthQueryHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommandHandlerFactoryInterface;
use ProBillerNG\Transaction\Domain\Model\InMemoryRepository;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\CircuitBreakerService;
use ProBillerNG\Transaction\Infrastructure\Application\Services\LookupThreeDsTwoCommandHandlerFactory;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ApcCircuitBreakerVerifier;
use Redis;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use Zend\Config\Config;
use Zend\Di\ServiceLocator;

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Transaction Health DTO
        $this->app->bind(TransactionHealthDTOAssembler::class, HttpQueryHealthDTOAssembler::class);

        $this->app->bind(
            LookupThreeDsTwoCommandHandlerFactoryInterface::class,
            LookupThreeDsTwoCommandHandlerFactory::class
        );

        // Repository
        $this->app->bind(
            TransactionRepository::class,
            function () {
                return new FirestoreTransactionRepository(
                    new FirestoreClient(
                        [
                            'projectId' => env('GOOGLE_CLOUD_PROJECT', 'mg-probiller-dev')
                        ]
                    ),
                    $this->app->make(FirestoreSerializer::class)
                );
            }
        );

        // Circuit Breaker Service
        $this->app
            ->when(RetrieveTransactionHealthQueryHandler::class)
            ->needs(CircuitBreakerService::class)
            ->give(ApcCircuitBreakerVerifier::class);

        // Circuit breaker configuration
        $this->app->singleton(
            CommandFactory::class,
            function (Application $app) {
                $config = new Config($app['config']->get("phystrix"));

                $stateStorage          = new ApcStateStorage();
                $circuitBreakerFactory = new CircuitBreakerFactory($stateStorage);
                $commandMetricsFactory = new CommandMetricsFactory($stateStorage);

                $commandFactory = new CommandFactory(
                    $config,
                    new ServiceLocator(),
                    $circuitBreakerFactory,
                    $commandMetricsFactory,
                    new RequestCache(),
                    new RequestLog()
                );

                return $commandFactory;
            }
        );

        //BILogger
        $this->app->singleton(
            BILoggerService::class,
            function () {
                $biLogger = new BILoggerService();
                $biLogger->initializeConfig(storage_path() . '/logs/' . env('BI_LOG_FILE'));
                return $biLogger;
            }
        );

        // DTO
        $this->app
            ->when(AbortTransactionCommandHandler::class)
            ->needs(AbortTransactionDTOAssembler::class)
            ->give(AbortHttpCommandDTOAssembler::class);

        // Redis
        $this->app->singleton(
            Redis::class,
            function () {
                $redis = new Redis();
                $redis->connect(
                    env('REDIS_HOST'),
                    env('REDIS_PORT')
                );

                $redis->auth(env('REDIS_PASS'));
                $redis->setOption(REDIS::OPT_PREFIX, (string) env('REDIS_PREFIX'));

                return $redis;
            }
        );

        $this->app->bind(
            InMemoryRepository::class,
            RedisRepository::class
        );
    }
}
