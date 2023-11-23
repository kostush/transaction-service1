<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use DateInterval;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveChargeTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\CreatesTransactionData;
use Tests\Faker;
use Tests\UnitTestCase;

class RetrieveChargeTransactionReturnTypeTest extends UnitTestCase
{
    use Faker;
    use CreatesTransactionData;

    /**
     * @var array
     */
    private $billerInteractionData;

    /**
     * @var array
     */
    private $threeDS2billerResponse;

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $date = new \DateTimeImmutable();
        $this->billerInteractionData = [
            'type'      => 'response',
            'payload'   => json_encode(
                [
                    "authNo"             => "230069",
                    "merchantInvoiceID"  => "4165c1cddd83a9cb8.99590556",
                    "merchantAccount"    => "1",
                    "approvedAmount"     => "1.08",
                    "cardIssuerPhone"    => "800-432-3117 OR 800-935-9935",
                    "cardLastFour"       => "1111",
                    "cardIssuerURL"      => "HTTP:\/\/WWW.JPMORGANCHASE.COM",
                    "version"            => "1.0",
                    "merchantCustomerID" => "4165c1cddd82cce24.92280817",
                    "cvv2Code"           => "M",
                    "reasonCode"         => "0",
                    "retrievalNo"        => "100016ae443d36b",
                    "cardIssuerName"     => "JPMORGAN CHASE BANK, N.A.",
                    "payType"            => "CREDIT",
                    "cardHash"           => $_ENV['ROCKETGATE_CARD_HASH_1'],
                    "cardDebitCredit"    => "1",
                    "cardRegion"         => "1,2",
                    "cardDescription"    => "CREDIT",
                    "cardCountry"        => "US",
                    "cardType"           => "VISA",
                    "bankResponseCode"   => "0",
                    "approvedCurrency"   => "USD",
                    "guidNo"             => "100016AE443D36B",
                    "cardExpiration"     => "1130",
                    "responseCode"       => "0"
                ],
                JSON_THROW_ON_ERROR
            ),
            'createdAt' => $date
        ];

        $this->threeDS2billerResponse = [
            'type'      => 'response',
            'payload' => json_encode(
                [
                    "merchantInvoiceID"=>"4165c1cddd83a9cb8.99590556",
                    "merchantAccount"=>"7",
                    "approvedAmount"=>"123.0",
                    "cardLastFour"=>"0955",
                    "version"=>"1.0","
                    _3DSECURE_STEP_UP_URL"=>"https://dev-secure.rocketgate.com/hostedpage/3DSimulator_2_0.jsp",
                    "merchantCustomerID"=>"4165c1cddd82cce24.92280667",
                    "reasonCode"=>"225",
                    "transactionTime"=>"2021-02-16 10:46:31",
                    "payType"=>"CREDIT",
                    "cardHash"=>$_ENV['ROCKETGATE_CARD_HASH_1'],
                    "_3DSECURE_STEP_UP_JWT"=>"https=://stage-ord-purchase-gateway.probiller.com/api/v1/purchase/threed/complete/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9",
                    "cardDebitCredit"=>"2",
                    "cardRegion"=>"5",
                    "cardDescription"=>"CREDIT",
                    "cardCountry"=>"BR",
                    "cardType"=>"VISA",
                    "_3DSECURE_VERSION"=>"2.2.0",
                    "approvedCurrency"=>"USD",
                    "guidNo"=>"1000177AB85889B",
                    "cardExpiration"=>"0523",
                    "responseCode"=>"2"
                ]
            ),
            'createdAt' => $date->sub(new DateInterval('P1D'))
        ];
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);
        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction($this->createBillerInteraction($this->threeDS2billerResponse));
        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));
        $transaction->updateThreedsVersion(2);

        $transactionReturnType = RetrieveChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveChargeTransactionReturnType::class, $transactionReturnType);

        $reflection = new \ReflectionClass($transactionReturnType);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionReturnType);
        }

        return [$transaction, $result];
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_from_entity_should_return_a_charge_transaction_payload_when_correct_data_is_provided(): void
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $transactionPayload = RetrieveChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveChargeTransactionReturnType::class, $transactionPayload);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_biller_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerId(), $result['billerId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_merchant_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantId(), $result['merchantId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_merchant_password($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantPassword(), $result['merchantPassword']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_invoice_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantInvoiceId(), $result['invoiceId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_customer_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantCustomerId(), $result['customerId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_card_hash($creationData): void
    {
        list($transaction, $result) = $creationData;

        $billerInteractions = $transaction->billerInteractions();
        $billerInteraction  = $billerInteractions[3];
        $payload            = json_decode($billerInteraction->payload(), true);

        $this->assertEquals(stripcslashes($result['cardHash']), stripcslashes($payload['cardHash']));
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_transaction_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        //TODO this field has been deprecated in favor of billerTransactionId and will be removed
        $this->assertArrayHasKey('transactionId', $result);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_biller_transaction_id($creationData): void
    {
        list($transaction, $result) = $creationData;

        $billerInteractions = $transaction->billerInteractions();
        $billerInteraction  = $billerInteractions[3];
        $payload            = json_decode($billerInteraction->payload(), true);

        $this->assertEquals($result['billerTransactionId'], $payload['guidNo']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_currency($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->chargeInformation()->currency(), $result['currency']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_site_id($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->siteId(), $result['siteId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_payment_type($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->paymentType(), $result['paymentType']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_merchant_account($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantAccount(), $result['merchantAccount']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_member($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertArrayHasKey('member', $result);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_transaction($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertArrayHasKey('transaction', $result);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction_with_code_as_null(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertNull($result['transaction']->code());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction_with_reason_as_null(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertNull($result['transaction']->reason());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_card_description($creationData): void
    {
        /** @var $transaction Transaction */
        list($transaction, $result) = $creationData;

        /** @var  $billerInteractions */
        $billerInteractions = $transaction->billerInteractions();
        $billerInteraction  = $billerInteractions[3];
        $payload            = json_decode($billerInteraction->payload(), true);

        $this->assertEquals(stripcslashes($result['cardDescription']), stripcslashes($payload['cardDescription']));
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function it_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode($this->createRocketgateBillerResponse()),
                new \DateTimeImmutable(),
            )
        );

        $transactionReturnType = RetrieveChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveChargeTransactionReturnType::class, $transactionReturnType);

        $reflection = new \ReflectionClass($transactionReturnType);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionReturnType);
        }

        return [$transaction, $result];
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends it_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction
     * @return void
     */
    public function it_should_return_provided_transaction_with_valid_code(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertSame(0, $result['transaction']->code());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends it_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction
     * @return void
     */
    public function it_should_return_provided_transaction_with_valid_reason(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertSame('Success', $result['transaction']->reason());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_threeD_version($creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->threedsVersion(), $result['threedSecuredVersion']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_secured_with_threeD_true($creationData): void
    {
        list($transaction, $result) = $creationData;

        $this->assertTrue($result['securedWithThreeD']);
    }
}
