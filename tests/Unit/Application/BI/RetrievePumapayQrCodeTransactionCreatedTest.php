<?php
declare(strict_types=1);

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use Tests\UnitTestCase;

class RetrievePumapayQrCodeTransactionCreatedTest extends UnitTestCase
{
    /**
     * @var BillerResponse
     */
    private $billerResponseMock;

    /**
     * @var string
     */
    private $testSiteId = "4c22fba2-f883-11e8-8eb2-f2801f1b9fd1";

    /**
     * @return void
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->billerResponseMock = $this->createMock(PumapayBillerResponse::class);
    }

    /**
     * @test
     * @return ChargeTransactionCreated
     * @throws \Exception
     */
    public function it_should_return_a_new_base_event(): ChargeTransactionCreated
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createChargeTransactionWithoutRebillOnPumapay(["siteId" => $this->testSiteId]),
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY
        );

        $this->assertInstanceOf(ChargeTransactionCreated::class, $transactionCreatedEvent);

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     *
     * @return void
     */
    public function it_should_have_a_timestamp(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('timestamp', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     *
     * @return void
     */
    public function it_should_have_siteId(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('siteId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_bin_routing(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('None', $transactionCreatedEvent->getValue()['binRouting']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_biller_transaction_id(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('None', $transactionCreatedEvent->getValue()['billerTransactionId']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     * @throws \Exception
     */
    public function it_should_have_payment_template_flag_set_to_no(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('NO', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_transaction_type(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('charge', $transactionCreatedEvent->getValue()['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_previous_transaction_id_set_to_null(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertNull($transactionCreatedEvent->getValue()['previousTransactionId']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_action(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('None', $transactionCreatedEvent->getValue()['action']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_free_sale(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame(0, $transactionCreatedEvent->getValue()['freeSale']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_payment_type(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('crypto', $transactionCreatedEvent->getValue()['paymentType']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_transaction_state(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame('TransactionPending', $transactionCreatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_biller_name_capitalized(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame(ucfirst(PumaPayBillerSettings::PUMAPAY), $transactionCreatedEvent->getValue()['biller']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_biller_response_date_flag(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('billerResponseDate', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_transaction_id_flag(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('transactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_session_id_flag(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('sessionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return void
     */
    public function it_should_have_the_right_version(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertSame(ChargeTransactionCreated::LATEST_VERSION, $transactionCreatedEvent->getValue()['version']);
    }
}
