<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use Tests\UnitTestCase;

class PumapayJoinPostbackCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_have_the_status_key_when_json_serialize_is_called(): void
    {
        $status = $this->createMock(Approved::class);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('status')->willReturn($status);

        $dto = new PumapayJoinPostbackCommandHttpDTO($transactionMock);

        $this->assertArrayHasKey('status', $dto->jsonSerialize());
    }
}
