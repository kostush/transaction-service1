<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\OtherPaymentTypeInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateOtherPaymentTypeChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\OtherPaymentTypeChargeClient;
use Tests\UnitTestCase;

/**
 * @group otherPayment
 * Class RocketgateOtherPaymentTypeChargeAdapterTest
 * @package Tests\Unit\Infrastructure\Domain\Services
 */
class RocketgateOtherPaymentTypeChargeAdapterTest extends UnitTestCase
{
    /**
     * @var MockObject|OtherPaymentTypeChargeClient
     */
    private $mockClient;

    /** @var ChargeTransaction */
    private $chargeTransaction;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPaymentMethodException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = $this->createMock(OtherPaymentTypeChargeClient::class);

        $billerSettings = RocketGateChargeSettings::create(
            (string) $this->faker->numberBetween(1, 100),
            $this->faker->password,
            'merchantCustomerId',
            'invoiceId',
            (string) $this->faker->numberBetween(1, 100),
            (string) $this->faker->numberBetween(1, 100),
            $this->faker->uuid,
            'descriptor',
            $this->faker->ipv4,
            (string) $this->faker->numberBetween(1, 100),
            $this->faker->word,
            false
        );

        $payment = new Payment(
            (string) 'check',
            new OtherPaymentTypeInformation(
                'routingNumber',
                'accountNumber',
                false,
                '1234',
                null,
                'checks'
            )
        );

        $this->chargeTransaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $this->faker->uuid,
            null,
            RocketGateBillerSettings::ROCKETGATE,
            $this->faker->currencyCode,
            $payment,
            $billerSettings,
            false
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_create_aborted_response_if_receive_exception_from_client(): void
    {
        $this->mockClient
            ->method('chargeOtherPaymentType')
            ->willThrowException(new \Exception());

        $adapter         = new RocketgateOtherPaymentTypeChargeAdapter($this->mockClient);
        $abortedResponse = $adapter->charge($this->chargeTransaction);
        $this->assertTrue($abortedResponse->aborted());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_create_approved_response_when_receive_success_response_from_biller(): void
    {
        $this->mockClient
            ->method('chargeOtherPaymentType')
            ->willReturn($this->successResponseFromBiller());

        $adapter         = new RocketgateOtherPaymentTypeChargeAdapter($this->mockClient);
        $abortedResponse = $adapter->charge($this->chargeTransaction);

        $this->assertTrue($abortedResponse->approved());
    }

    /**
     * @return string
     */
    private function successResponseFromBiller(): string
    {
        return '{"request":{"version":"P6.6m","merchantID":"' . $_ENV['ROCKETGATE_MERCHANT_ID_1'] . '",
        "merchantPassword":"2","merchantCustomerID":"4",
        "merchantInvoiceID":"5","customerFirstName":"Joanny Schultz",
        "customerLastName":"Smitham","billingAddress":"9795 Franco Hill\nWest Ludieton, NV 85710",
        "billingCity":"Hoegerfurt","billingState":"Pennsylvania","billingZipCode":"64241",
        "billingCountry":"US","email":{},"routingNo":"999999999","accountNo":"112233",
        "savingsAccount":false,"ssNumber":"1111","amount":"26.58","scrub":"IGNORE",
        "transactionType":"CC_CONFIRM","referenceGUID":"1000176AE864D9B"},
        "response":{"approvedAmount":"26.58","retrievalNo":"1609245805984",
        "approvedCurrency":"USD","reasonCode":"0","merchantInvoiceID":"245feb2466b5bab0.66104193",
        "merchantAccount":"2","version":"1.0","scrubResults":"NEGDB=0,PROFILE=0,ACTIVITY=0",
        "cardLastFour":"2233","PINLESS":"TRUE","cardHash":"' . $_ENV['ROCKETGATE_CARD_HASH_1'] . '",
        "guidNo":"1000176AE864D9B","responseCode":"0","merchantCustomerID":"245feb2466b5ba79.87165096",
        "payType":"DEBIT"},"code":"0","reason":"0"}';
    }
}
