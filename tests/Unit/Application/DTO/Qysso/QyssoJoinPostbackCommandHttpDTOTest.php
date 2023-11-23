<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;
use Tests\UnitTestCase;

class QyssoJoinPostbackCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     */
    public function it_should_an_array_when_json_serialize_is_called(): array
    {
        $status = $this->createMock(Approved::class);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('status')->willReturn($status);
        $transactionMock->method('paymentType')->willReturn('cc');

        $dto = new QyssoJoinPostbackCommandHttpDTO($transactionMock);

        $this->assertIsArray($dto->jsonSerialize());

        return $dto->jsonSerialize();
    }

    /**
     * @test
     * @depends it_should_an_array_when_json_serialize_is_called
     * @param array $response Response array
     * @return void
     */
    public function it_should_contain_all_keys(array $response): void
    {
        $okFlag     = true;
        $neededKeys = [
            'status',
            'paymentType',
            'paymentMethod'
        ];

        foreach ($neededKeys as $key) {
            if (!isset($response[$key])) {
                $okFlag = false;
                break;
            }
        }

        $this->assertTrue($okFlag);
    }
}
