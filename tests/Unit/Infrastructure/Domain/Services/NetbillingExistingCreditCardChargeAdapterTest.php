<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingExistingCreditCardChargeAdapter;
use Tests\UnitTestCase;

class NetbillingExistingCreditCardChargeAdapterTest extends UnitTestCase
{
    /** @var NetbillingClient */
    private $mockClient;

    /** @var NetbillingCreditCardChargeTranslator */
    private $mockTranslator;

    /** @var CreditCardChargeCommand */
    private $mockCommand;

    /**
     * setup method
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->mockClient     = $this->createMock(NetbillingClient::class);
        $this->mockTranslator = $this->createMock(NetbillingCreditCardChargeTranslator::class);
        $this->mockCommand    = $this->createMock(CreditCardChargeCommand::class);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_call_existing_card_charge_on_client_with_command(): void
    {
        $this->mockClient->expects($this->once())->method('chargeExistingCreditCard')->with($this->mockCommand);
        $adapter = new NetbillingExistingCreditCardChargeAdapter($this->mockClient, $this->mockTranslator);
        $adapter->charge($this->mockCommand, new \DateTimeImmutable());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_call_translator_method_with_the_response_from_client(): void
    {
        $this->mockClient->method('chargeExistingCreditCard')->willReturn('{}');
        $this->mockTranslator->expects($this->once())->method('toCreditCardBillerResponse')->with('{}', $this->anything(), $this->anything());
        $adapter = new NetbillingExistingCreditCardChargeAdapter($this->mockClient, $this->mockTranslator);
        $adapter->charge($this->mockCommand, new \DateTimeImmutable());
    }
}
