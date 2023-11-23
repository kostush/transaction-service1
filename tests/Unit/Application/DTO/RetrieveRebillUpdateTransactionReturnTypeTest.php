<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\CreatesTransactionData;
use Tests\Faker;
use Tests\UnitTestCase;

class RetrieveRebillUpdateTransactionReturnTypeTest extends UnitTestCase
{
    use Faker;

    use CreatesTransactionData;

    /**
     * @var array
     */
    private $billerInteractionData;

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
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
            'createdAt' => new \DateTimeImmutable()
        ];
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided(
    ): array
    {
        $transaction = $this->createUpdateRebillTransaction();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));

        $transactionReturnType = RetrieveRebillUpdateTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveRebillUpdateTransactionReturnType::class, $transactionReturnType);

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
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_biller_id(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerId(), $result['billerId']);
    }

    /**
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_merchant_id(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantId(), $result['merchantId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_merchant_password(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantPassword(), $result['merchantPassword']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_invoice_id(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantInvoiceId(), $result['invoiceId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_customer_id(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertEquals($transaction->billerChargeSettings()->merchantCustomerId(), $result['customerId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_biller_transaction_id(array $creationData): void
    {
        list($transaction, $result) = $creationData;

        $billerInteractions = $transaction->billerInteractions();
        $billerInteraction  = $billerInteractions[0];
        $payload            = json_decode($billerInteraction->payload(), true);

        $this->assertEquals($result['billerTransactionId'], $payload['guidNo']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction(array $creationData): void
    {
        list($transaction, $result) = $creationData;
        $this->assertArrayHasKey('transaction', $result);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     * @throws \ReflectionException
     */
    public function it_should_return_provided_transaction_status(array $creationData): void
    {
        list($transaction, $result) = $creationData;

        $reflection = new \ReflectionClass($result['transaction']);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $transactionResult[$prop->getName()] = $prop->getValue($result['transaction']);
        }

        $this->assertSame((string) $transaction->status(), $transactionResult['status']);
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return void
     * @throws \ReflectionException
     */
    public function it_should_return_provided_transaction_payment_detailed_information(array $creationData): void
    {
        list($transaction, $result) = $creationData;

        $reflection = new \ReflectionClass($result['transaction']);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $transactionResult[$prop->getName()] = $prop->getValue($result['transaction']);
        }

        $this->assertSame(
            $transaction->paymentInformation()->detailedInformation(),
            $transactionResult['paymentDetailedInformation']
        );
    }

    /**
     * @test
     * @param array $creationData Transaction Data and Result
     * @depends it_should_return_a_retrieve_rebill_update_transaction_return_type_when_correct_data_is_provided
     * @return array
     */
    public function it_should_return_a_previous_transaction_id(array $creationData): array
    {
        list($transaction, $result) = $creationData;
        $this->assertArrayHasKey('previousTransactionId', $result);
        return $creationData;
    }

    /**
     * @test
     * @depends it_should_return_a_previous_transaction_id
     */
    public function it_should_return_the_correct_previous_transaction_id(array $creationData): void
    {
        /** @var Transaction $transaction */
        list($transaction, $result) = $creationData;
        $this->assertSame((string) $transaction->previousTransactionId(), $result['previousTransactionId']);
    }
}
