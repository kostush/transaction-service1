<?php
declare(strict_types=1);

namespace Tests\System;

use Odesk\Phystrix\ApcStateStorage;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionHealthQueryHandler;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochNewSaleCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStartUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStopUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateSuspendCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateUpdateRebillCommand;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class RetrieveTransactionHealthTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $uri = '/api/v1/healthCheck';

    /**
     * Setup method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function retrieve_transaction_health_should_return_success()
    {
        $response = $this->json('GET', $this->uri);
        $response->assertResponseStatus(Response::HTTP_OK);

        return json_decode($this->response->getContent(), true);
    }

    /**
     * @test
     * @depends retrieve_transaction_health_should_return_success
     * @param array|null $response Data
     * @return void
     */
    public function returned_transaction_health_should_contain_status_key($response)
    {
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * @test
     * @depends retrieve_transaction_health_should_return_success
     * @param array|null $response Data
     * @return void
     */
    public function returned_transaction_health_should_have_ok_status($response)
    {
        $this->assertSame(RetrieveTransactionHealthQueryHandler::HEALTH_OK, $response['status']);
    }

    /**
     * @test
     * @depends retrieve_transaction_health_should_return_success
     * @param array|null $response Data
     * @return void
     */
    public function returned_transaction_health_should_have_billers($response)
    {
        $this->assertArrayHasKey('billers', $response);
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_rocketgate_suspended_circuit_breaker_is_open(
    ): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateSuspendCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateSuspendCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::ROCKETGATE]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateSuspendCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_rocketgate_start_circuit_breaker_is_open(
    ): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStartUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStartUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::ROCKETGATE]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStartUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_rocketgate_stop_circuit_breaker_is_open(
    ): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStopUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStopUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::ROCKETGATE]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateStopUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_rocketgate_update_circuit_breaker_is_open(
    ): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::ROCKETGATE]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateUpdateRebillCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_rocketgate_threeD_circuit_breaker_is_open(
    ): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateCompleteThreeDCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateCompleteThreeDCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::ROCKETGATE]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . MakeRocketgateCompleteThreeDCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }

    /**
     * @test
     * @return void
     */
    public function returned_transaction_health_should_have_error_status_when_epoch_circuit_breaker_is_open(): void
    {
        $initialStatus = (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . EpochNewSaleCommand::class . ApcStateStorage::OPENED_NAME
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . EpochNewSaleCommand::class . ApcStateStorage::OPENED_NAME,
            true
        );

        $this->json('GET', $this->uri);
        $result = json_decode($this->response->getContent(), true);

        $this->assertSame(
            RetrieveTransactionHealthQueryHandler::HEALTH_DOWN,
            $result['billers'][RocketGateBillerSettings::EPOCH]
        );

        apc_store(
            ApcStateStorage::CACHE_PREFIX . EpochNewSaleCommand::class . ApcStateStorage::OPENED_NAME,
            $initialStatus
        );
    }
}
