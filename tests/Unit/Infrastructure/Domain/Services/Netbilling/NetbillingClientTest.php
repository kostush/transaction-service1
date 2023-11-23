<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Netbilling\Application\Services\CancelRebillCommandHandler;
use ProBillerNG\Netbilling\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Netbilling\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;
use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingClient;
use Tests\UnitTestCase;

class NetbillingClientTest extends UnitTestCase
{
    /**
     * @var ChargeWithNewCreditCardCommandHandler
     */
    private $newCreditCardChargeHandler;

    /**
     * @var ChargeWithExistingCreditCardCommandHandler
     */
    private $existingCreditCardChargeHandler;

    /**
     * @var UpdateRebillCommandHandler
     */
    private $updateRebillCommandHandler;

    /**
     * @var CancelRebillCommandHandler::class
     */
    private $cancelRebillCommandHandler;

    /**
     * @var NetbillingClient
     */
    private $client;

    /**
     * Setup before test
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->newCreditCardChargeHandler = $this->createMock(
            ChargeWithNewCreditCardCommandHandler::class
        );

        $this->existingCreditCardChargeHandler = $this->createMock(
            ChargeWithExistingCreditCardCommandHandler::class
        );

        $this->updateRebillCommandHandler = $this->createMock(
            UpdateRebillCommandHandler::class
        );

        $this->cancelRebillCommandHandler = $this->createMock(
            CancelRebillCommandHandler::class
        );

        $this->client = new NetbillingClient(
            $this->newCreditCardChargeHandler,
            $this->existingCreditCardChargeHandler,
            $this->updateRebillCommandHandler,
            $this->cancelRebillCommandHandler
        );
    }

    /**
     * @test
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function charge_new_credit_card_should_throw_a_service_exception_when_an_error_occurs_in_netbilling_service()
    {
        $this->expectException(NetbillingServiceException::class);

        $this->newCreditCardChargeHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->chargeNewCreditCard(new CreditCardChargeCommand('1', [], [], [], [], []));
    }

    /**
     * @test
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function charge_existing_credit_card_should_throw_a_service_exception_when_an_error_occurs_in_netbilling_service()
    {
        $this->expectException(NetbillingServiceException::class);

        $this->existingCreditCardChargeHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->chargeExistingCreditCard(new CreditCardChargeCommand('1', [], [], [], [], []));
    }

    /**
     * @test
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function update_rebill_should_throw_a_service_exception_when_an_error_occurs_in_netbilling_service()
    {
        $this->expectException(NetbillingServiceException::class);

        $this->updateRebillCommandHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->updateRebill(new UpdateRebillCommand(
                '1',
                '2',
                'siteTag',
                'controlKeyword',
                '20',
                10
                )
        );
    }

    /**
     * @test
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function charge_new_credit_card_should_accept_only_new_credit_card_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->chargeNewCreditCard(
            $this->createMock(ChargeWithExistingCreditCardCommand::class)
        );
    }

    /**
     * @test
     * @throws NetbillingServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function update_rebill_should_accept_only_new_credit_card_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->updateRebill($this->createMock(ChargeWithExistingCreditCardCommand::class));
    }
}
