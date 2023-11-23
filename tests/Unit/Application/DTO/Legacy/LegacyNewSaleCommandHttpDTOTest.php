<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Legacy;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Pending;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyNewSaleCommandHttpDTOTest
 * @package Tests\Unit\Application\DTO\Legacy
 */
class LegacyNewSaleCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function legacy_dto_should_give_array_when_json_serialize_is_called(): array
    {
        $status = $this->createMock(Pending::class);
        $status->method('__toString')->willReturn('Pending');
        $status->method('pending')->willReturn(true);

        $transactionMock = $this->createMock(ChargeTransaction::class);
        $transactionMock->method('status')->willReturn($status);
        $transactionMock->method('transactionId')->willReturn(TransactionId::create());
        $transactionMock->method('responsePayload')->willReturn(
            json_encode(
                [
                    LegacyNewSaleCommandHttpDTO::REDIRECT_URL => 'http://redirect-url'
                ]
            )
        );

        $dto = new LegacyNewSaleCommandHttpDTO($transactionMock);

        $this->assertIsArray($dto->jsonSerialize());

        return $dto->jsonSerialize();
    }

    /**
     * @test
     * @depends legacy_dto_should_give_array_when_json_serialize_is_called
     * @param array $response Response array
     * @return void
     */
    public function it_should_contain_all_keys(array $response): void
    {
        $okFlag     = true;
        $neededKeys = [
            'transactionId',
            'status',
            'redirectUrl',
            'sessionId',
            'traceId',
            'correlationId'
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
