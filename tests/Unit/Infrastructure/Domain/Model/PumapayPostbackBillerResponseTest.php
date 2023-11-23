<?php

declare(strict_types=1);

namespace Tests\Unit\Infastructure\Domain\Model;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;
use Tests\UnitTestCase;

class PumapayPostbackBillerResponseTest extends UnitTestCase
{
    protected $billerTransactionId = 'some-random-id-string';

    /**
     * @test
     * @return PumapayPostbackBillerResponse
     * @throws \Exception
     */
    public function it_should_return_a_valid_object(): PumapayPostbackBillerResponse
    {
        $billerResponse = PumapayPostbackBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'status' => 'approved',
                    'type' => 'join',
                    'response' => [
                        'transactionData' => [
                            'id' => $this->billerTransactionId
                        ]
                    ]
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertInstanceOf(PumapayPostbackBillerResponse::class, $billerResponse);

        return $billerResponse;
    }

    /**
     * @test
     * @depends it_should_return_a_valid_object
     * @param PumapayPostbackBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function status_should_return_the_exact_same_string(
        PumapayPostbackBillerResponse $billerResponse
    ): void {
        $this->assertSame('approved', $billerResponse->status());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_object
     * @param PumapayPostbackBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function type_should_return_the_exact_same_string(
        PumapayPostbackBillerResponse $billerResponse
    ): void {
        $this->assertSame('join', $billerResponse->type());
    }

    /**
     * @test
     * @depends it_should_return_a_valid_object
     * @param PumapayPostbackBillerResponse $billerResponse Biller Response
     * @return void
     */
    public function biller_transaction_id_should_return_the_exact_same_string(
        PumapayPostbackBillerResponse $billerResponse
    ): void {
        $this->assertSame($this->billerTransactionId, $billerResponse->billerTransactionId());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function biller_transaction_id_should_return_null_when_no_transaction_data_on_response_property_defined(): void
    {
        $billerResponse = PumapayPostbackBillerResponse::create(
            new \DateTimeImmutable(),
            json_encode(
                [
                    'status' => 'approved',
                    'type' => 'join',
                    'response' => []
                ],
                JSON_THROW_ON_ERROR
            ),
            new \DateTimeImmutable()
        );

        $this->assertNull($billerResponse->billerTransactionId());
    }
}
