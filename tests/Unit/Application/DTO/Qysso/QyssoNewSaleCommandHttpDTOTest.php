<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\QyssoNewSaleCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use Tests\UnitTestCase;

class QyssoNewSaleCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     */
    public function it_should_an_array_when_json_serialize_is_called(): array
    {
        $status = $this->createMock(Pending::class);
        $status->method('__toString')->willReturn('Pending');
        $status->method('pending')->willReturn(true);

        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('status')->willReturn($status);
        $transactionMock->method('transactionId')->willReturn(TransactionId::create());
        $transactionMock->method('responsePayload')->willReturn(
            json_encode([
                'D3Redirect' => 'http://redirect-url'
            ])
        );


        $dto = new QyssoNewSaleCommandHttpDTO([$transactionMock, $transactionMock]);

        $this->assertIsArray($dto->jsonSerialize());

        return $dto->jsonSerialize();
    }

    /**
     * @test
     * @depends it_should_an_array_when_json_serialize_is_called
     * @param array $response Response array
     */
    public function it_should_contain_all_keys(array $response)
    {
        $okFlag     = true;
        $neededKeys = [
            'transactionId',
            'status',
            'redirectUrl'
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
