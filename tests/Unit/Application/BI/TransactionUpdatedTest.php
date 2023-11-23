<?php
declare(strict_types=1);

namespace Tests\Unit\Application\BI;

use ProBillerNG\Transaction\Application\BI\TransactionUpdated;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use Tests\UnitTestCase;

class TransactionUpdatedTest extends UnitTestCase
{
    /**
     * @var BillerResponse
     */
    private $billerResponseMock;

    protected $billerTransactionId = 'some-iden-str';

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->billerResponseMock = $this->createMock(PumapayPostbackBillerResponse::class);
        $this->billerResponseMock->method('responseDate')->willReturn(new \DateTimeImmutable());
        $this->billerResponseMock->method('billerTransactionId')->willReturn($this->billerTransactionId);
    }

    /**
     * @test
     * @return TransactionUpdated
     * @throws \Exception
     */
    public function it_should_return_a_transaction_updated_event(): TransactionUpdated
    {
        $transactionCreatedEvent = new TransactionUpdated(
            $this->createChargeTransactionWithoutRebillOnPumapay(),
            $this->billerResponseMock,
            PumaPayBillerSettings::PUMAPAY,
            BillerSettings::ACTION_POSTBACK
        );

        $this->assertInstanceOf(TransactionUpdated::class, $transactionCreatedEvent);

        return $transactionCreatedEvent;
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_have_all_keys(TransactionUpdated $transactionUpdatedEvent): void
    {
        $biKey = [
            'version',
            'timestamp',
            'sessionId',
            'transactionId',
            'paymentType',
            'billerTransactionId',
            'billerResponseDate',
            'biller',
            'transactionState',
            'transactionType',
            'freeSale',
            'action',
            'previousTransactionId'
        ];

        $eventArr = $transactionUpdatedEvent->getValue();

        foreach ($biKey as $keyName) {
            if (!array_key_exists($keyName, $eventArr)) {
                $this->assertTrue(false, $keyName . ' key was not found inside $eventArr');
                break;
            }
        }

        $this->assertTrue(true);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_return_the_exact_payment_type(TransactionUpdated $transactionUpdatedEvent): void
    {
        $this->assertSame(PaymentType::CRYPTO, $transactionUpdatedEvent->getValue()['paymentType']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_return_the_exact_biller_transaction_id(TransactionUpdated $transactionUpdatedEvent): void
    {
        $this->assertSame($this->billerTransactionId, $transactionUpdatedEvent->getValue()['billerTransactionId']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_return_biller_response_date(TransactionUpdated $transactionUpdatedEvent): void
    {
        $this->assertNotNull($transactionUpdatedEvent->getValue()['billerResponseDate']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_return_the_exact_transaction_state(TransactionUpdated $transactionUpdatedEvent): void
    {
        $this->assertSame('TransactionPending', $transactionUpdatedEvent->getValue()['transactionState']);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_updated_event
     * @param TransactionUpdated $transactionUpdatedEvent TransactionUpdated
     * @return void
     */
    public function it_should_return_free_sale_as_zero(TransactionUpdated $transactionUpdatedEvent): void
    {
        $this->assertSame(0, $transactionUpdatedEvent->getValue()['freeSale']);
    }
}
