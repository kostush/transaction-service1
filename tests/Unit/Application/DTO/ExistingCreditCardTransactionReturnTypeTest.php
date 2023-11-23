<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\ExistingCreditCardTransactionReturnType;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\Faker;
use Tests\UnitTestCase;

class ExistingCreditCardTransactionReturnTypeTest extends UnitTestCase
{
    use Faker;

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();

        $transactionPayloadCreditCardType = ExistingCreditCardTransactionReturnType::createFromTransaction($transaction);

        $this->assertInstanceOf(ExistingCreditCardTransactionReturnType::class, $transactionPayloadCreditCardType);

        $reflection = new \ReflectionClass($transactionPayloadCreditCardType);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionPayloadCreditCardType);
        }

        return [$result, $transaction];
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_minimum_data_is_provided(): void
    {
        $transactionPayloadCreditCardType = ExistingCreditCardTransactionReturnType::createFromTransaction(
            $this->createPendingRocketgateTransactionSingleCharge()
        );

        $this->assertInstanceOf(
            ExistingCreditCardTransactionReturnType::class,
            $transactionPayloadCreditCardType
        );
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_transactionId($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['transactionId'], $transaction->transactionId());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_not_return_first6($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertArrayNotHasKey('first6', $transactionPayloadCreditCardType);
    }

    /**
     * @test
     * @param array $array Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_not_return_last4($array): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $array;
        $this->assertArrayNotHasKey('last4', $transactionPayloadCreditCardType);
    }

    /**
     * @test
     * @param array $array Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_status($array): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $array;
        $this->assertEquals((string) $transaction->status(), $transactionPayloadCreditCardType['status']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_amount($creationData): void
    {
        [$transactionPayloadCreditCardType,$transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['amount'], $transaction->chargeInformation()->amount());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_created_at($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['createdAt'], $transaction->createdAt()->format('Y-m-d H:i:s'));
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_rebill_amount($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['rebillAmount'], $transaction->chargeInformation()->rebill()->amount());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_rebill_frequency($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['rebillFrequency'], $transaction->chargeInformation()->rebill()->frequency());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_provided_rebill_start($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertEquals($transactionPayloadCreditCardType['rebillStart'], $transaction->chargeInformation()->rebill()->start());
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function existing_credit_card_return_type_should_return_card_hash_usage_flag($creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertTrue($transactionPayloadCreditCardType['paymentTemplateUsed']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_code_as_null_if_transaction_is_not_declined(array $creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertNull($transactionPayloadCreditCardType['code']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_from_transaction_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided
     * @return void
     */
    public function it_should_return_reason_as_null_if_transaction_is_not_declined(array $creationData): void
    {
        [$transactionPayloadCreditCardType, $transaction] = $creationData;
        $this->assertNull($transactionPayloadCreditCardType['reason']);
    }

    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function it_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction(): array
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode($this->createRocketgateBillerResponse()),
                new \DateTimeImmutable(),
            )
        );

        $transactionPayloadCreditCardType = ExistingCreditCardTransactionReturnType::createFromTransaction($transaction);
        $transactionPayloadCreditCardType->setCode(100);
        $transactionPayloadCreditCardType->setReason('Declined transaction');

        $this->assertInstanceOf(ExistingCreditCardTransactionReturnType::class, $transactionPayloadCreditCardType);

        $reflection = new \ReflectionClass($transactionPayloadCreditCardType);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionPayloadCreditCardType);
        }

        return [$result, $transaction];
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends it_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction
     * @return void
     */
    public function it_should_return_code_if_transaction_is_declined(array $creationData): void
    {
        list($transactionPayloadCreditCardType, $transaction) = $creationData;
        $this->assertSame(100, $transactionPayloadCreditCardType['code']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends it_should_return_an_existing_credit_card_transaction_return_type_when_correct_data_is_provided_for_a_declined_transaction
     * @return void
     */
    public function it_should_return_reason_if_transaction_is_declined($creationData): void
    {
        list($transactionPayloadCreditCardType, $transaction) = $creationData;
        $this->assertSame('Declined transaction', $transactionPayloadCreditCardType['reason']);
    }
}
