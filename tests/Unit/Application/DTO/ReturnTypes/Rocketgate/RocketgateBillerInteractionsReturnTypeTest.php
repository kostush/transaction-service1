<?php

namespace Tests\Unit\Application\DTO\ReturnTypes\Rocketgate;

use Probiller\Common\Enums\AddBillerInteractionType\BillerInteractionType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerTransaction;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use Tests\UnitTestCase;

class RocketgateBillerInteractionsReturnTypeTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     */
    public function it_should_return_a_rocketgate_biller_transaction_object_when_approved_amount_is_set(): void
    {
        $result = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $this->billerInteractionCollection(), false
        );

        $billerInteractionRequest  = $this->createBillerInteraction(['type' => BillerInteraction::TYPE_REQUEST]);
        $billerInteractionResponse = $this->createBillerInteraction(['type' => BillerInteraction::TYPE_RESPONSE]);
        $transaction       = $result::buildRocketgateBillerTransaction($billerInteractionRequest, $billerInteractionResponse,false);

        $this->assertInstanceOf(RocketgateBillerTransaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     */
    public function it_should_return_a_rocketgate_biller_transaction_object_when_approved_amount_is_not_set(): void
    {
        $result = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $this->billerInteractionCollection(), false
        );

        $data = [
            'payload' => json_encode(
                [
                    'cvv2Check' => true,
                    'amount' => 6.99,
                    'currency' => 'USD',
                    'cardNo' => $this->faker->creditCardNumber,
                    'expireMonth' => 1,
                    'expireYear' => 2020,
                    'cvv' => 523,
                    'cardNumber' => $this->faker->creditCardNumber,
                    'cardCvv2' => 523,
                    'merchantID' => $_ENV['ROCKETGATE_MERCHANT_ID_3'],
                    'merchantPassword' => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_3'],
                    'merchantSiteID' => 7,
                    'merchantAccount' => 1,
                    'merchantProductID' => '42456483 - a6ae - 4ed7 - b43f - e19635697f28',
                    'merchantCustomerID' => '4165c1cddd82cce24.92280115',
                    'merchantInvoiceID' => '4165c1cddd83a9cb8.99590115',
                    'ipAddress' => '205.45.120.42',
                    'transactionType' => 'CC_CONFIRM',
                    'referenceGUID' => '1000168B8329577',
                ],
                JSON_THROW_ON_ERROR
            )
        ];

        $billerInteractionRequest  = $this->createBillerInteraction($data);
        $billerInteractionResponse = $this->createBillerInteraction(['type' => BillerInteraction::TYPE_RESPONSE]);
        $transaction               = $result::buildRocketgateBillerTransaction($billerInteractionRequest, $billerInteractionResponse,false);

        $this->assertInstanceOf(RocketgateBillerTransaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     */
    public function it_should_should_contain_correct_invoice_id_and_customer_id(): void
    {
        $invoiceId  = $this->faker->uuid;
        $customerId = $this->faker->uuid;

        $requestInteraction = BillerInteraction::create(
            BillerInteraction::TYPE_REQUEST,
            '{}',
            new \DateTimeImmutable()
        );

        $responseInteraction = BillerInteraction::create(
            BillerInteraction::TYPE_RESPONSE,
            '{
                "invoiceId":"' . $invoiceId . '",
                "customerId":"' . $customerId . '",
                "approvedAmount":"1.0",
                "guidNo":"100017DD80259D7"
            }',
            new \DateTimeImmutable(),
        );

        $rgBillerTransaction = RocketgateBillerInteractionsReturnType::buildRocketgateBillerTransaction(
            $requestInteraction,
            $responseInteraction
        );

        $this->assertSame($invoiceId, $rgBillerTransaction->invoiceId());
        $this->assertSame($customerId, $rgBillerTransaction->customerId());
    }
}
