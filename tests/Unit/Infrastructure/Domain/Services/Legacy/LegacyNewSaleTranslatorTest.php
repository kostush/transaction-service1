<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Legacy;

use DateTimeImmutable;
use Exception;
use ProbillerNG\LegacyServiceClient\Model\ErrorInformation;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlRequest;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\LegacyServiceResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleTranslator;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyNewSaleTranslatorTest
 * @package Tests\Unit\Infrastructure\Domain\Services\Legacy
 */
class LegacyNewSaleTranslatorTest extends UnitTestCase
{

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_throw_exception_when_the_response_is_error_information(): void
    {
        $this->expectException(LegacyServiceResponseException::class);
        $response = $this->createMock(ErrorInformation::class);
        $request  = $this->createMock(GeneratePurchaseUrlRequest::class);
        (new LegacyNewSaleTranslator())
            ->translate($response, $request, new DateTimeImmutable(), new DateTimeImmutable());
    }
}
