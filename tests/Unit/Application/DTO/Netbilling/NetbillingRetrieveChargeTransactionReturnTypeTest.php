<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Netbilling;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingChargeTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use Tests\CreateTransactionDataForNetbilling;
use Tests\Faker;
use Tests\UnitTestCase;

class NetbillingRetrieveChargeTransactionReturnTypeTest extends UnitTestCase
{
    use Faker;
    use CreateTransactionDataForNetbilling;

    /**
     * @var array
     */
    private $billerInteractionData;

    /**
     * @var array
     */
    private $billerInteractionRequestData;

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
                    "avs_code" => "X",
                    "cvv2_code" => "M",
                    "status_code" => "1",
                    "processor" => "TEST",
                    "auth_code" => "999999",
                    "settle_amount" => "5.00",
                    "settle_currency" => "USD",
                    "trans_id" => "113841072523",
                    "auth_msg" => "TEST APPROVED",
                    "auth_date" => "2019-12-19 16:35:29"
                ],
                JSON_THROW_ON_ERROR
            ),
            'createdAt' => new \DateTimeImmutable()
        ];

        $this->billerInteractionRequestData = [
            'type' => BillerInteraction::TYPE_REQUEST,
            'payload' => '{"memberId": "1"}'
        ];
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided(): array
    {
        $transaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionRequestData));

        $transactionReturnType = RetrieveNetbillingChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveNetbillingChargeTransactionReturnType::class, $transactionReturnType);

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
        $transaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionRequestData));

        $transactionPayload = RetrieveNetbillingChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveNetbillingChargeTransactionReturnType::class, $transactionPayload);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_biller_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->billerId(), $result['billerId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_site_id($creationData): void
    {
        [$transaction, $result] = $creationData;
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
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->paymentType(), $result['paymentType']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_type_should_return_provided_transaction($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertArrayHasKey('transaction', $result);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function charge_transaction_return_should_return_provided_biller_member_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertArrayHasKey('billerMemberId', $result);
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionRequestData));

        $transactionReturnType = RetrieveNetbillingChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveNetbillingChargeTransactionReturnType::class, $transactionReturnType);

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
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided
     * @return void
     */
    public function existing_charge_transaction_return_type_should_return_provided_biller_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->billerId(), $result['billerId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided
     * @return void
     */
    public function existing_charge_transaction_return_type_should_return_provided_site_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->siteId(), $result['siteId']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided
     * @return void
     */
    public function existing_charge_transaction_return_type_should_return_provided_payment_type($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->paymentType(), $result['paymentType']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided
     * @return void
     */
    public function existing_charge_transaction_return_type_should_return_provided_transaction($creationData): void
    {
        [$transaction, $result] = $creationData;
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
        [$transaction, $result] = $creationData;
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
        [$transaction, $result] = $creationData;
        $this->assertNull($result['transaction']->reason());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_with_existing_card_when_correct_data_is_provided
     * @return void
     */
    public function existing_charge_transaction_return_type_should_return_provided_biller_member_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertArrayHasKey('billerMemberId', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function it_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction(): array
    {
        $transaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));

        $transaction->updateTransactionFromNetbillingResponse(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode($this->createDeclinedNetbillingBillerResponse()),
                new \DateTimeImmutable(),
            )
        );

        $transactionReturnType = RetrieveNetbillingChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveNetbillingChargeTransactionReturnType::class, $transactionReturnType);

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
        [$transaction, $result] = $creationData;
        $this->assertSame(9999, $result['transaction']->code());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends it_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction
     * @return void
     */
    public function it_should_return_provided_transaction_with_valid_reason(array $creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertSame('TEST APPROVED', $result['transaction']->reason());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_missing_biller_interaction()
    {
        $transaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();

        $data = [
            'netbilling' => [
                'billerMemberId' => 1,
                'transId' => 2
            ]
        ];

        $transaction->setSubsequentOperationFields(json_encode($data));

        $transactionReturnType = RetrieveNetbillingChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrieveNetbillingChargeTransactionReturnType::class, $transactionReturnType);

        $reflection = new \ReflectionClass($transactionReturnType);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionReturnType);
        }

        $this->assertNotEmpty($result['billerTransactions']);
    }
}
