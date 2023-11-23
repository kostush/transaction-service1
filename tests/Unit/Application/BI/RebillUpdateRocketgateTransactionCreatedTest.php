<?php
declare(strict_types=1);

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\RebillUpdateTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use Tests\UnitTestCase;

class RebillUpdateRocketgateTransactionCreatedTest extends UnitTestCase
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
        $this->billerResponseMock = $this->createMock(RocketgateBillerResponse::class);
        $this->billerResponseMock->method('responseDate')->willReturn(new \DateTimeImmutable());
        $this->billerResponseMock->method('billerTransactionId')->willReturn('100016A02ZZZZZZ');
    }

    /**
     * @test
     * @return RebillUpdateTransactionCreated
     * @throws \Exception
     */
    public function it_should_return_a_new_rebill_update_transaction_created_event(): RebillUpdateTransactionCreated
    {
        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $this->createUpdateRebillTransaction(),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertInstanceOf(RebillUpdateTransactionCreated::class, $transactionCreatedEvent);

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_bin_routing_none(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertEquals(
            RebillUpdateTransactionCreated::NO_BIN_ROUTING,
            $transactionCreatedEvent->getValue()['binRouting']
        );
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_payment_type_cc(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertEquals(
            'CreditCard',
            $transactionCreatedEvent->getValue()['paymentType']
        );
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_free_sale_0(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertEquals(0, $transactionCreatedEvent->getValue()['freeSale']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_a_timestamp(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('timestamp', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_a_biller_transaction_id(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerTransactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_payment_template_set_to_yes(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertSame('YES', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_the_correct_transaction_type(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertSame('rebill_update', $transactionCreatedEvent->getValue()['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_rebill_update_transaction_created_event
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent RebillUpdateTransactionCreated
     * @return void
     */
    public function it_should_have_previous_transaction_id(
        RebillUpdateTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('previousTransactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_approved_state(): void
    {
        $transaction = $this->createUpdateRebillTransaction();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );
        $transaction->approve();

        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );
        $this->assertEquals('TransactionApproved', $transactionCreatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_aborted_state(): void
    {
        $transaction = $this->createUpdateRebillTransaction();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );
        $transaction->abort();

        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );
        $this->assertEquals('TransactionAborted', $transactionCreatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @return RebillUpdateTransactionCreated $transactionCreatedEvent transaction event
     * @throws \Exception
     */
    public function event_generated_from_a_declined_cancel_rebill_transaction_should_have_the_correct_state(): RebillUpdateTransactionCreated
    {
        $this->billerResponseMock->method('declined')->willReturn(true);
        $transaction = $this->createUpdateRebillTransaction();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '1',
                        'code'     => '111',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );
        $transaction->decline();

        $transactionCreatedEvent = new RebillUpdateTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );
        $this->assertEquals('TransactionDeclined', $transactionCreatedEvent->getValue()['transactionState']);
        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends event_generated_from_a_declined_cancel_rebill_transaction_should_have_the_correct_state
     * @param RebillUpdateTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function event_generated_from_a_declined_rebill_transaction_should_have_reason_code_decline(RebillUpdateTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('reasonCodeDecline', $transactionCreatedEvent->getValue());
    }
}