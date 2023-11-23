<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use Tests\UnitTestCase;

class BillerInteractionTest extends UnitTestCase
{
    /**
     * @var array
     */
    private $data;

    /**
     * @throws \Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
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
                    'merchantID' => 1366400931,
                    'merchantPassword' => 'GBasdfadfa',
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
            ),
            'createdAt' => new \DateTimeImmutable()
        ];
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws InvalidBillerInteractionPayloadException
     */
    public function create_should_return_exception_when_incorrect_payload_data_is_provided()
    {
        $this->expectException(InvalidBillerInteractionPayloadException::class);

        $this->createBillerInteraction(['payload' => '{ value: "test" }']);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws InvalidBillerInteractionTypeException
     */
    public function create_should_return_exception_when_incorrect_type_is_provided()
    {
        $this->expectException(InvalidBillerInteractionTypeException::class);

        $this->createBillerInteraction(['type' => 'invalidType']);
    }

    /**
     * @test
     * @return BillerInteraction
     * @throws \Exception
     */
    public function create_should_return_biller_information_when_correct_data_is_provided()
    {
        $billerInteraction = $this->createBillerInteraction($this->data);

        $this->assertInstanceOf(BillerInteraction::class, $billerInteraction);

        return $billerInteraction;
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     * @throws \Exception
     */
    public function biller_information_should_return_true_when_equal_biller_information(
        BillerInteraction $billerInteraction
    ) {
        $equalBillerInteraction = $this->createBillerInteraction($this->data);

        $this->assertTrue($billerInteraction->equals($equalBillerInteraction));
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     * @throws \Exception
     */
    public function biller_information_should_return_false_when_not_equal(BillerInteraction $billerInteraction)
    {
        $equalBillerInteraction = $this->createBillerInteraction(['type' => BillerInteraction::TYPE_RESPONSE]);

        $this->assertFalse($billerInteraction->equals($equalBillerInteraction));
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     */
    public function biller_information_should_have_card_no_obfuscated(BillerInteraction $billerInteraction)
    {
        $returnedPayload = json_decode($billerInteraction->payload(), true);
        $this->assertSame(self::OBFUSCATED_STRING, $returnedPayload['cardNo']);
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     */
    public function biller_information_should_have_cvv_obfuscated(BillerInteraction $billerInteraction)
    {
        $returnedPayload = json_decode($billerInteraction->payload(), true);
        $this->assertSame(self::OBFUSCATED_STRING, $returnedPayload['cvv']);
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     */
    public function biller_information_should_have_card_number_obfuscated(BillerInteraction $billerInteraction)
    {
        $returnedPayload = json_decode($billerInteraction->payload(), true);
        $this->assertSame(self::OBFUSCATED_STRING, $returnedPayload['cardNumber']);
    }

    /**
     * @test
     * @depends create_should_return_biller_information_when_correct_data_is_provided
     * @param BillerInteraction $billerInteraction BillerInteraction object
     * @return void
     */
    public function biller_information_should_have_cardCvv2_obfuscated(BillerInteraction $billerInteraction)
    {
        $returnedPayload = json_decode($billerInteraction->payload(), true);
        $this->assertSame(self::OBFUSCATED_STRING, $returnedPayload['cardCvv2']);
    }
}
