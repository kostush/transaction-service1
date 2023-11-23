<?php
declare(strict_types=1);

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\ChargeTransactionCreated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use Tests\UnitTestCase;

class ChargeRocketgateTransactionCreatedTest extends UnitTestCase
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
     * @return ChargeTransactionCreated
     * @throws \Exception
     */
    public function it_should_return_a_new_base_event(): ChargeTransactionCreated
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(["siteId"=>$this->testSiteId]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );
        $this->assertInstanceOf(ChargeTransactionCreated::class, $transactionCreatedEvent);

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     * @return void
     */
    public function it_should_should_have_a_timestamp(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('timestamp', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     * @return void
     */
    public function it_should_should_have_siteId(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('siteId', $transactionCreatedEvent->getValue());
        $this->assertEquals($this->testSiteId, $transactionCreatedEvent->getValue()['siteId']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_bin_routing(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('binRouting', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_a_biller_transaction_id(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerTransactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return ChargeTransactionCreated
     */
    public function it_should_have_payment_template_flag(
        ChargeTransactionCreated $transactionCreatedEvent
    ): ChargeTransactionCreated {
        $this->assertArrayHasKey('paymentTemplate', $transactionCreatedEvent->getValue());

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     * @throws \Exception
     */
    public function it_should_have_payment_template_flag_set_to_no(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertEquals('NO', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @return void
     * @throws \Exception
     */
    public function it_should_have_payment_template_flag_set_to_yes(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingTransactionWithRebillForExistingCreditCard(),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals('YES', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_the_correct_transaction_type(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertEquals('charge', $transactionCreatedEvent->getValue()['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_previous_transaction_id_set_to_null(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertEquals(null, $transactionCreatedEvent->getValue()['previousTransactionId']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_required_3ds_set_to_false(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertFalse($transactionCreatedEvent->getValue()['requiredToUse3D']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_transaction_with_3d_set_to_false(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertFalse($transactionCreatedEvent->getValue()['transactionWith3D']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_have_three_ds_version_set_to_null(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertNull($transactionCreatedEvent->getValue()['threedsVersion']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_an_event_with_bin_routing_none(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['merchantAccount' => null]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals(ChargeTransactionCreated::NO_BIN_ROUTING, $transactionCreatedEvent->getValue()['binRouting']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_credit_card(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['method' => 'cc']),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals('CreditCard', $transactionCreatedEvent->getValue()['paymentType']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_one_as_free_sale(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['amount' => 0]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals(1, $transactionCreatedEvent->getValue()['freeSale']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_zero_as_free_sale(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['amount' => 0.1]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals(0, $transactionCreatedEvent->getValue()['freeSale']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_transaction_state_approved(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();
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

        $transactionCreatedEvent = new ChargeTransactionCreated(
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
    public function it_should_return_transaction_state_aborted(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );
        $transaction->abort();

        $transactionCreatedEvent = new ChargeTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals('TransactionAborted', $transactionCreatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_transaction_state_aborted_when_billeresponse_equal_null(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::createAbortedResponse(
                new RocketgateServiceException()
            )
        );
        $transaction->abort();

        $transactionCreatedEvent = new ChargeTransactionCreated(
            $transaction,
            null,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals('TransactionAborted', $transactionCreatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @return ChargeTransactionCreated $transactionCreatedEvent transaction event
     * @throws \Exception
     */
    public function it_should_create_new_transaction_declined_event_with_status_declined_should_return_transaction_state_declined(): ChargeTransactionCreated
    {
        $this->billerResponseMock->method('declined')->willReturn(true);
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();
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

        $transactionCreatedEvent = new ChargeTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertEquals('TransactionDeclined', $transactionCreatedEvent->getValue()['transactionState']);
        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreated
     * @return void
     */
    public function it_should_create_new_transaction_created_event_should_have_a_timestamp(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('timestamp', $transactionCreatedEvent->getValue());
    }


    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_create_new_transaction_created_event_should_bin_routing(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('binRouting', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_create_new_transaction_declined_event_with_status_declined_should_return_transaction_state_declined
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_create_new_transaction_created_event_should_have_reason_code_decline(ChargeTransactionCreated $transactionCreatedEvent): void
    {
        $this->assertArrayHasKey('reasonCodeDecline', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_create_new_transaction_created_event_should_have_a_biller_transaction_id(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertArrayHasKey('billerTransactionId', $transactionCreatedEvent->getValue());
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     *
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     *
     * @return ChargeTransactionCreated
     */
    public function it_should_create_new_credit_card_transaction_created_event_should_have_payment_template_flag(
        ChargeTransactionCreated $transactionCreatedEvent
    ): ChargeTransactionCreated {
        $this->assertArrayHasKey('paymentTemplate', $transactionCreatedEvent->getValue());

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     * @throws \Exception
     */
    public function it_should_create_new_credit_card_transaction_created_event_should_have_payment_template_flag_set_to_no(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertSame('NO', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @return void
     * @throws \Exception
     */
    public function it_should_create_existing_credit_card_transaction_created_event_should_have_payment_template_flag_set_to_yes(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingTransactionWithRebillForExistingCreditCard(),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertSame('YES', $transactionCreatedEvent->getValue()['paymentTemplate']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_create_existing_credit_card_transaction_created_event_should_have_the_correct_transaction_type(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertSame('charge', $transactionCreatedEvent->getValue()['transactionType']);
    }

    /**
     * @test
     * @depends it_should_return_a_new_base_event
     * @param ChargeTransactionCreated $transactionCreatedEvent TransactionCreate
     * @return void
     */
    public function it_should_create_existing_credit_card_transaction_created_event_should_have_previous_transaction_id_set_to_null(
        ChargeTransactionCreated $transactionCreatedEvent
    ): void {
        $this->assertSame(null, $transactionCreatedEvent->getValue()['previousTransactionId']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_have_required_3ds_set_to_true(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['useThreeD' => true]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertTrue($transactionCreatedEvent->getValue()['requiredToUse3D']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_have_transaction_with_3ds_set_to_true(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(['useThreeD' => true]),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertTrue($transactionCreatedEvent->getValue()['transactionWith3D']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_have_three_ds_version_set_to_one(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(
                [
                    'useThreeD'      => true,
                    'threedsVersion' => 1,
                ]
            ),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertSame(1, $transactionCreatedEvent->getValue()['threedsVersion']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_have_three_ds_version_set_to_two(): void
    {
        $transactionCreatedEvent = new ChargeTransactionCreated(
            $this->createPendingRocketgateTransactionSingleCharge(
                [
                    'useThreeD'      => true,
                    'threedsVersion' => 2,
                ]
            ),
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertSame(2, $transactionCreatedEvent->getValue()['threedsVersion']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_have_transaction_with_3d_set_to_false_when_require_is_true(): void
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge(['useThreeD' => true]);
        $transaction->updateTransactionWith3D(false);

        $transactionCreatedEvent = new ChargeTransactionCreated(
            $transaction,
            $this->billerResponseMock,
            RocketGateBillerSettings::ROCKETGATE
        );

        $this->assertFalse($transactionCreatedEvent->getValue()['transactionWith3D']);
    }
}
