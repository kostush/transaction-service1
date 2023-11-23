<?php

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use Tests\UnitTestCase;

class RebillUpdatePumapayTransactionCreatedTest extends UnitTestCase
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
    public function it_should_return_a_transaction_created_bi_event(): array
    {
        $previousTransaction     = $this->createChargeTransactionWithoutRebillOnPumapay();
        $rebillUpdateTransaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);

        $biEvent = new RebillUpdateTransactionCreated(
            $rebillUpdateTransaction,
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY,
            BillerSettings::ACTION_POSTBACK
        );

        $this->assertInstanceOf(RebillUpdateTransactionCreated::class, $biEvent);

        return $biEvent->getValue();
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_bin_routing_none(
        array $transactionCreatedEvent
    ): void {
        $this->assertEquals(RebillUpdateTransactionCreated::NO_BIN_ROUTING, $transactionCreatedEvent['binRouting']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_type_rebill_update(
        array $transactionCreatedEvent
    ): void {
        $this->assertEquals(RebillUpdateTransactionCreated::TRANSACTION_TYPE, $transactionCreatedEvent['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_action_postback(
        array $transactionCreatedEvent
    ): void {
        $this->assertEquals(BillerSettings::ACTION_POSTBACK, $transactionCreatedEvent['action']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_pumapay_name_capitalized(
        array $transactionCreatedEvent
    ): void {
        $this->assertEquals(ucfirst(PumaPayBillerSettings::PUMAPAY), $transactionCreatedEvent['biller']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_previous_transaction_id(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('previousTransactionId', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_payment_template(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('paymentTemplate', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_state(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('paymentTemplate', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_pending_state(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('transactionState', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_biller_response_date(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerResponseDate', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_biller_transaction_id(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerTransactionId', $transactionCreatedEvent);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_created_bi_event
     * @param array $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_transaction_id(
        array $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('transactionId', $transactionCreatedEvent);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_have_the_correct_state(): void
    {
        $previousTransaction     = $this->createChargeTransactionWithoutRebillOnPumapay();
        $rebillUpdateTransaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);

        $rebillUpdateTransaction->updatePumapayTransactionFromBillerResponse(
            PumapayPostbackBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'status' => 'approved',
                        'type' => 'join',
                        'response' => [
                            'transactionData' => [
                                'id' => $this->faker->uuid
                            ]
                        ]
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable(),

                )
        );

        $rebillUpdateTransaction->approve();

        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $rebillUpdateTransaction,
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY,
            BillerSettings::ACTION_POSTBACK
        );

        $this->assertEquals('TransactionApproved', $transactionCreatedEvent->getValue()['transactionState']);
    }
}
