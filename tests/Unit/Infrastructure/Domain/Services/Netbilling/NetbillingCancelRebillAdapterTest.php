<?php
declare(strict_types=1);

namespace Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardChargeTranslator;
use Prophecy\Argument;
use Tests\UnitTestCase;
use DateTimeImmutable;
use TypeError as TypeError;

class NetbillingCancelRebillAdapterTest extends UnitTestCase
{
    /**
     * @var NetbillingClient
     */
    private $netbillingMockClient;

    /**
     * @var NetbillingCreditCardChargeTranslator
     */
    private $netbillingMockTranslator;

    /**
     * @var CancelRebillCommand
     */
    private $cancelRebillMockCommand;

    /**
     * @var NetbillingBillerResponse
     */
    private $netbillingMockBillerResponse;

    /**
     * setup method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->netbillingMockClient     = $this->prophesize(NetbillingClient::class);
        $this->netbillingMockTranslator = $this->prophesize(NetbillingCreditCardChargeTranslator::class);
        $this->cancelRebillMockCommand              = $this->prophesize(CancelRebillCommand::class);
        $this->netbillingMockBillerResponse = $this->prophesize(NetbillingBillerResponse::class);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerResponseException
     * @throws NetbillingServiceException
     * @throws \Throwable
     */
    public function it_should_call_the_client_and_the_translator_and_return_a_netbilling_biller_response(): void
    {
        $this->netbillingMockClient->cancelRebill($this->cancelRebillMockCommand->reveal())->shouldBeCalled();
        $this->netbillingMockClient->cancelRebill($this->cancelRebillMockCommand->reveal())->willReturn('json');

        $this->netbillingMockTranslator->toCreditCardBillerResponse(
            'json',
            Argument::type(DateTimeImmutable::class),
            Argument::type(DateTimeImmutable::class)
        )->shouldBeCalled()->willReturn($this->netbillingMockBillerResponse->reveal());

        $adapter = new NetbillingCancelRebillAdapter(
            $this->netbillingMockClient->reveal(),
            $this->netbillingMockTranslator->reveal()
        );

        $adapter->cancel($this->cancelRebillMockCommand->reveal(), new DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_incorrect_command_argument_is_passed(): void
    {
        $this->expectException(TypeError::class);

        $adapter = new NetbillingCancelRebillAdapter(
            $this->netbillingMockClient->reveal(),
            $this->netbillingMockTranslator->reveal()
        );
        $adapter->cancel('invalidCommand', new DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_incorrect_date_time_argument_is_passed(): void
    {
        $this->expectException(TypeError::class);

        $adapter = new NetbillingCancelRebillAdapter(
            $this->netbillingMockClient->reveal(),
            $this->netbillingMockTranslator->reveal()
        );
        $adapter->cancel($this->cancelRebillMockCommand, new \DateTime());
    }
}