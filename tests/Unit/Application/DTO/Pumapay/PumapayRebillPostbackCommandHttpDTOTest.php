<?php

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class PumapayRebillPostbackCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     */
    public function it_should_have_the_status_key_when_json_serialize_is_called(): array
    {
        $status = $this->createMock(Approved::class);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('status')->willReturn($status);

        $dto = new \ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayRebillPostbackCommandHttpDTO($transactionMock);

        $this->assertArrayHasKey('status', $dto->jsonSerialize());

        return $dto->jsonSerialize();
    }

    /**
     * @test
     * @depends it_should_have_the_status_key_when_json_serialize_is_called
     * @param array $dto DTO
     * @return void
     */
    public function it_should_have_the_transaction_id_key_when_json_serialize_is_called(array $dto): void
    {
        $this->assertArrayHasKey('transactionId', $dto);
    }

}