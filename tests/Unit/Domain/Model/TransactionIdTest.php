<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use Tests\UnitTestCase;

class TransactionIdTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_should_return_a_transaction_id_object()
    {
        $transactionId = TransactionId::create();

        $this->assertInstanceOf(TransactionId::class, $transactionId);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_from_string_should_return_a_transaction_id_object()
    {
        $transactionId = TransactionId::createFromString($this->faker->uuid);

        $this->assertInstanceOf(TransactionId::class, $transactionId);
    }

    /**
     * @test
     * @return void
     * @throws InvalidTransactionInformationException
     */
    public function create_from_string_should_throw_exception_when_invalid_uuid_received()
    {
        $this->expectException(InvalidTransactionInformationException::class);

        TransactionId::createFromString('8051d60a-7fb0-4ef2');
    }
}
