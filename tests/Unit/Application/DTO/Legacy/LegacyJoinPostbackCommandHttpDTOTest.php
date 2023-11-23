<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO\Legacy;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyJoinPostbackCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyJoinPostbackCommandHttpDTOTest
 * @package Tests\Unit\Application\DTO\Legacy
 */
class LegacyJoinPostbackCommandHttpDTOTest extends UnitTestCase
{
    /**
     * @test
     * @return array
     * @throws \Exception
     */
    public function legacy_postback_response_should_be_an_array_when_json_serialize_is_called(): array
    {
        $status = $this->createMock(Approved::class);

        $transactionMock = $this->createMock(ChargeTransaction::class);
        $transactionMock->method('status')->willReturn($status);
        $transactionMock->method('paymentType')->willReturn('cc');

        $dto = new LegacyJoinPostbackCommandHttpDTO($transactionMock);

        $this->assertIsArray($dto->jsonSerialize());

        return $dto->jsonSerialize();
    }

    /**
     * @test
     * @depends legacy_postback_response_should_be_an_array_when_json_serialize_is_called
     * @param array $response Response array
     * @return void
     */
    public function legacy_postback_response_should_contain_all_keys(array $response): void
    {
        $okFlag     = true;
        $neededKeys = [
            'transactionId',
            'status',
            'paymentType',
            'paymentMethod',
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
