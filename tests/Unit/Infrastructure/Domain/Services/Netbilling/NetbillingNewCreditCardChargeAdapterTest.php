<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingNewCreditCardChargeAdapter;
use Tests\UnitTestCase;
use Prophecy\Argument;

class NetbillingNewCreditCardChargeAdapterTest extends UnitTestCase
{
    /**
     * @var NetbillingClient
     */
    private $mockClient;

    /**
     * @var NetbillingCreditCardChargeTranslator
     */
    private $mockTranslator;

    /**
     * @var CreditCardChargeCommand
     */
    private $mockCommand;

    /**
     * @var NetbillingBillerResponse
     */
    private $mockBillerResponse;

    /**
     * setup method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->mockClient         = $this->prophesize(NetbillingClient::class);
        $this->mockTranslator     = $this->prophesize(NetbillingCreditCardChargeTranslator::class);
        $this->mockCommand        = $this->prophesize(CreditCardChargeCommand::class);
        $this->mockBillerResponse = $this->prophesize(NetbillingBillerResponse::class);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException
     * @throws \Exception
     */
    public function it_should_call_the_client_and_the_translator_and_return_a_netbilling_biller_response(): void
    {
        //because I am forced to mock both class dependencies method calls with exact arguments and the return value
        //there is no point in creating separate tests for each expectancy
        //since this test will ensure the method execution flow can't be changed without it failing
        $this->mockClient->chargeNewCreditCard($this->mockCommand->reveal())->shouldBeCalled();
        $this->mockClient->chargeNewCreditCard($this->mockCommand->reveal())->willReturn('json');

        $this->mockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(\DateTimeImmutable::class),
            Argument::type(\DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->mockBillerResponse->reveal());

        $adapter = new NetbillingNewCreditCardChargeAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );

        $adapter->charge($this->mockCommand->reveal(), new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException
     * @return void
     */
    public function it_should_throw_exception_if_incorrect_command_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new NetbillingNewCreditCardChargeAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->charge('invalid', new \DateTimeImmutable());
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException
     */
    public function it_should_throw_exception_if_incorrect_date_time_argument_is_passed(): void
    {
        $this->expectException(\TypeError::class);

        $adapter = new NetbillingNewCreditCardChargeAdapter(
            $this->mockClient->reveal(),
            $this->mockTranslator->reveal()
        );
        $adapter->charge($this->mockCommand, new \DateTime());
    }
}
