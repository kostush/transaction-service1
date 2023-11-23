<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Exception;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\EpochNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingPrepareBillerFieldsTrait;
use Tests\CreateTransactionDataForNetbilling;
use Tests\UnitTestCase;
use ProBillerNG\Logger\Exception as LoggerException;

class NetbillingPrepareBillerFieldsTraitTest extends UnitTestCase
{
    use NetbillingPrepareBillerFieldsTrait;
    use CreateTransactionDataForNetbilling;

    /** @var Transaction */
    protected $netbillingNewCardTransaction;

    /** @var Transaction */
    protected $netbillingExistingCardTransaction;

    /** @var int  */
    protected $expirationMonth;

    /**
     * @return void
     * @throws LoggerException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->expirationMonth = 3;

        $this->netbillingNewCardTransaction      = $this->createUpdateRebillNewCardNetbillingTransaction(
            [
                'expirationMonth' => $this->expirationMonth,
                'expirationYear'  => date('Y') + 1
            ]
        );
        $this->netbillingExistingCardTransaction = $this->createUpdateRebillExistingCardNetbillingTransaction();
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_site_tag_in_biller_fields():void
    {
        $result = $this->prepareBillerFields($this->netbillingNewCardTransaction);
        self::assertEquals($result['siteTag'], $_ENV['NETBILLING_SITE_TAG_2']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_the_correct_expiration_date():void
    {
        $result = $this->preparePaymentInformationFields($this->netbillingNewCardTransaction);
        self::assertEquals($result['cardExpire'], sprintf('0%d%d', $this->expirationMonth, date('y') + 1));
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_account_id_in_biller_fields():void
    {
        $result = $this->prepareBillerFields($this->netbillingNewCardTransaction);
        self::assertEquals($result['accountId'], $_ENV['NETBILLING_ACCOUNT_ID_2']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_routingCode_in_biller_fields():void
    {
        $result = $this->prepareBillerFields($this->netbillingNewCardTransaction);
        self::assertEquals($result['routingCode'], "INTC/12345");
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_amount_in_recurring_biller_fields():void
    {
        $result = $this->prepareRecurringBillingFields($this->netbillingNewCardTransaction);
        self::assertNotEmpty($result['rebillAmount']); // test generates random number
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_frequency_in_recurring_biller_fields():void
    {
        $result = $this->prepareRecurringBillingFields($this->netbillingNewCardTransaction);
        self::assertNotEmpty($result['rebillFrequency']); // test generates random number
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_card_hash_in_existing_payment_fields():void
    {
        $result = $this->prepareExistingPaymentInformationFields($this->netbillingExistingCardTransaction);
        self::assertEquals($result['cardNumber'], "CS:114621813929:" . $_ENV['NETBILLING_CARD_LAST_FOUR']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_amount_in_existing_payment_fields():void
    {
        $result = $this->prepareExistingPaymentInformationFields($this->netbillingExistingCardTransaction);
        self::assertNotEmpty($result['amount']); // test generates random number
    }

    /**
     * @test
     * @return void
     */
    public function it_should_have_pay_type_in_existing_payment_fields():void
    {
        $result = $this->prepareExistingPaymentInformationFields($this->netbillingExistingCardTransaction);
        self::assertEquals($result['payType'], "cc");
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_card_hash_in_new_payment_fields():void
    {
        $result = $this->preparePaymentInformationFields($this->netbillingNewCardTransaction);
        self::assertEquals($result['cardNumber'], $_ENV['ROCKETGATE_COMMON_CARD_NUMBER']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_amount_in_new_payment_fields():void
    {
        $result = $this->preparePaymentInformationFields($this->netbillingNewCardTransaction);
        self::assertNotEmpty($result['amount']); // test stub generates random number
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_expire_date_in_new_payment_fields():void
    {
        $result = $this->preparePaymentInformationFields($this->netbillingNewCardTransaction);
        self::assertNotEmpty($result['cardExpire']); // test stub generates random number
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_have_cvv2_in_new_payment_fields():void
    {
        $result = $this->preparePaymentInformationFields($this->netbillingNewCardTransaction);
        self::assertNotEmpty($result['cardCvv2']); // test stub generates random number
    }
}
