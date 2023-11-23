<?php

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use Tests\UnitTestCase;

class CancelRebillPumapayTransactionCreatedTest extends UnitTestCase
{
    /**
     * @var BillerResponse
     */
    private $billerResponseMock;

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->billerResponseMock = $this->createMock(PumapayBillerResponse::class);
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function it_should_return_a_rebill_update_transaction_created_bi_event(): array
    {
        $previousTransaction     = $this->createChargeTransactionWithoutRebillOnPumapay();
        $cancelRebillTransaction = RebillUpdateTransaction::createCancelRebillPumapayTransaction(
            $previousTransaction,
            'businessId',
            'businessModel',
            'apiKey'
        );
        
        $biEvent = new RebillUpdateTransactionCreated(
            $cancelRebillTransaction,
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY,
            BillerSettings::ACTION_CANCEL
        );

        $this->assertInstanceOf(RebillUpdateTransactionCreated::class, $biEvent);

        return $biEvent->getValue();
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_bin_routing_none(array $biEvent): void
    {
        $this->assertEquals(RebillUpdateTransactionCreated::NO_BIN_ROUTING, $biEvent['binRouting']);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_type_rebill_update(array $biEvent): void
    {
        $this->assertEquals(RebillUpdateTransactionCreated::TRANSACTION_TYPE, $biEvent['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_action_cancel(array $biEvent): void
    {
        $this->assertEquals(BillerSettings::ACTION_CANCEL, $biEvent['action']);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_pumapay_name_capitalized(array $biEvent): void
    {
        $this->assertEquals(ucfirst(PumaPayBillerSettings::PUMAPAY), $biEvent['biller']);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_previous_transaction_id(array $biEvent): void
    {
        $this->assertArrayHasKey('previousTransactionId', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_payment_template(array $biEvent): void
    {
        $this->assertArrayHasKey('paymentTemplate', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_state(array $biEvent): void
    {
        $this->assertArrayHasKey('paymentTemplate', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_pending_state(array $biEvent): void
    {
        $this->assertArrayHasKey('transactionState', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_biller_response_date(array $biEvent): void
    {
        $this->assertArrayHasKey('billerResponseDate', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_biller_transaction_id(array $biEvent): void
    {
        $this->assertArrayHasKey('billerTransactionId', $biEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_rebill_update_transaction_created_bi_event
     * @param array $biEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_id(array $biEvent): void
    {
        $this->assertArrayHasKey('transactionId', $biEvent);
    }

    /**
     * @test
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @return void
     */
    public function it_should_have_the_correct_state(): void
    {
        $previousTransaction     = $this->createChargeTransactionWithoutRebillOnPumapay();
        $cancelRebillTransaction = RebillUpdateTransaction::createCancelRebillPumapayTransaction(
            $previousTransaction,
            'businessId',
            'businessModel',
            'apiKey'
        );

        $cancelRebillTransaction->updatePumapayTransactionFromBillerResponse(
            PumapayCancelRebillBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'success'  => true,
                        'request'  => [
                            'businessId' => 'aaa',
                            'paymentId'  => 'bbb',
                        ],
                        'response' => [
                            'success' => true,
                            'status'  => 200
                        ],
                        'code'     => 200,
                        'reason'   => null
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $cancelRebillTransaction->approve();

        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $cancelRebillTransaction,
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY,
            BillerSettings::ACTION_CANCEL
        );

        $this->assertEquals('TransactionApproved', $transactionCreatedEvent->getValue()['transactionState']);
    }

}
