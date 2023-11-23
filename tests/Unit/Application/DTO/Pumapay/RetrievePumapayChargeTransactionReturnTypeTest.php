<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrievePumapayChargeTransactionReturnType;
use Tests\CreatesTransactionData;
use Tests\UnitTestCase;

class RetrievePumapayChargeTransactionReturnTypeTest extends UnitTestCase
{
    use CreatesTransactionData;

    /**
     * @var string
     */
    private $billerInteractionData;

    /**
     * @return void
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->billerInteractionData = [
            'type'      => 'response',
            'payload'   => json_encode(
                [
                    'status'   => 'approved',
                    'type'     => 'join',
                    'request'  => '',
                    'response' => [
                        'transactionData' => [
                            'statusID' => 3,
                            'typeID'   => 5,
                            'id'       => 'pZVUs7khzqn2kzweUc8ew1dAAKCgZbiJ',
                        ],
                    ],
                ],
                JSON_THROW_ON_ERROR
            ),
            'createdAt' => new \DateTimeImmutable(),
        ];
    }

    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided(): array
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();
        $transaction->addBillerInteraction($this->createBillerInteraction($this->billerInteractionData));

        $transactionReturnType = RetrievePumapayChargeTransactionReturnType::createFromEntity($transaction);

        $this->assertInstanceOf(RetrievePumapayChargeTransactionReturnType::class, $transactionReturnType);

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
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_biller_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->billerId(), $result['billerId']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        //TODO this field has been deprecated in favor of billerTransactionId and will be removed
        $this->assertSame((string) $transaction->transactionId(), $result['transaction']['transactionId']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_biller_transaction_id($creationData): void
    {
        [$transaction, $result] = $creationData;

        $billerInteractions = $transaction->billerInteractions();
        $billerInteraction  = $billerInteractions[0];
        $payload            = json_decode($billerInteraction->payload(), true);

        $this->assertEquals($result['billerTransactionId'], $payload['response']['transactionData']['id']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_currency($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->chargeInformation()->currency(), $result['currency']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_site_id($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->siteId(), $result['siteId']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_payment_type($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals($transaction->paymentType(), $result['paymentType']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_payment_status($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertEquals((string) $transaction->status(), $result['transaction']['status']);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertArrayHasKey('transaction', $result);
    }

    /**
     * @test
     *
     * @param array $creationData Transaction Member Payload and Payload
     *
     * @depends create_from_entity_should_return_a_retrieve_charge_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_provided_transaction_type($creationData): void
    {
        [$transaction, $result] = $creationData;
        $this->assertSame('join', $result['transaction']['type']);
    }
}
