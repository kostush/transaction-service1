<?php

declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use Odesk\Phystrix\RequestCache;
use Odesk\Phystrix\RequestLog;
use Zend\Config\Config;
use Odesk\Phystrix\ApcStateStorage;
use Odesk\Phystrix\CircuitBreakerFactory;
use Odesk\Phystrix\CommandFactory;
use Odesk\Phystrix\CommandMetricsFactory;
use PHPUnit\Framework\TestCase;
use ProBillerNG\Logger\Config\FileConfig;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\DomainEventCollection;
use Zend\Di\ServiceLocator;

abstract class UnitTestCase extends TestCase
{
    use CreatesTransactionData;
    use Faker;
    use LoadEnv;

    /**
     * obfuscated string
     */
    const OBFUSCATED_STRING = '*******';

    /**
     * Setup function, called before each test
     *
     * @return void
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConfigFormatException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConnectionException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerRetrieveException
     */
    protected function setUp(): void
    {
        $this->configLogger();
        $this->configFaker();
        $this->loadTestEnv();
        parent::setUp();
    }

    /**
     *
     * @return void
     */
    protected function configLogger()
    {
        $mockConfig = $this->createMock(FileConfig::class);
        Log::setConfig($mockConfig);
    }

    /**
     * @param string                $domainEventClassName domain class name I am looking for
     * @param DomainEventCollection $events               list of events
     * @return void
     */
    protected function assertDomainEventExists(string $domainEventClassName, DomainEventCollection $events)
    {
        $events->filter(
            function ($event) use ($domainEventClassName) {
                return get_class($event) == $domainEventClassName;
            }
        );
        $this->assertNotEmpty($events);
    }

    /**
     * @return CommandFactory
     */
    protected function getCircuitBreakerCommandFactory(): CommandFactory
    {
        $config = new Config(app('config')->get("phystrix"));

        $stateStorage          = new ApcStateStorage();
        $circuitBreakerFactory = new CircuitBreakerFactory($stateStorage);
        $commandMetricsFactory = new CommandMetricsFactory($stateStorage);

        return new CommandFactory(
            $config,
            new ServiceLocator(),
            $circuitBreakerFactory,
            $commandMetricsFactory,
            new RequestCache(),
            new RequestLog()
        );
    }
}
