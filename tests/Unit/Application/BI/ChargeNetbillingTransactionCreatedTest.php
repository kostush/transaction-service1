<?php
declare(strict_types=1);

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use Tests\CreateTransactionDataForNetbilling;
use Tests\UnitTestCase;

class ChargeNetbillingTransactionCreatedTest extends UnitTestCase
{
    use CreateTransactionDataForNetbilling;
    /**
     * @var BillerResponse
     */
    private $billerResponseMock;

    /**
     * @var string
     */
    private $testSiteId = "4c22fba2-f883-11e8-8eb2-f2801f1b9fd1";

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->billerResponseMock = $this->createMock(NetbillingBillerResponse::class);
        $this->billerResponseMock->method('responseDate')->willReturn(new \DateTimeImmutable());
        $this->billerResponseMock->method('billerTransactionId')->willReturn('114152087528');
    }

    /**
     * @test
     * @return ChargeTransactionCreated
     * @throws \Exception
     */
    public function transaction_created_event_should_return_a_new_base_event(): ChargeTransactionCreated
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createNetbillingPendingTransactionWithSingleCharge(["siteId"=>$this->testSiteId]),
            $this->billerResponseMock,
            NetbillingBillerSettings::NETBILLING
        );
        $this->assertInstanceOf(ChargeTransactionCreated::class, $transactionCreatedEvent);

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends transaction_created_event_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     * @return void
     */
    public function transaction_created_event_should_have_a_timestamp(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('timestamp', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends transaction_created_event_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     * @return void
     */
    public function transaction_created_event_should_have_siteId(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('siteId', $transactionCreatedEvent->getValue());
        $this->assertEquals($this->testSiteId, $transactionCreatedEvent->getValue()["siteId"]);
    }

    /**
     * @test
     * @depends transaction_created_event_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function transaction_created_event_should_have_a_biller_transaction_id(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerTransactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends transaction_created_event_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function transaction_created_event_should_have_bin_routing_code(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('binRouting', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function transaction_created_event_with_cc_payment_method_should_return_credit_card(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createNetbillingPendingTransactionWithSingleCharge(['method' => 'cc']),
            $this->billerResponseMock,
            NetbillingBillerSettings::NETBILLING
        );

        $this->assertEquals('CreditCard', $transactionCreatedEvent->getValue()['paymentType']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function transaction_created_event_with_amount_zero_should_return_free_sale_one(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createNetbillingPendingTransactionWithSingleCharge(['amount' => 0]),
            $this->billerResponseMock,
            NetbillingBillerSettings::NETBILLING
        );

        $this->assertEquals(1, $transactionCreatedEvent->getValue()['freeSale']);
    }
}
