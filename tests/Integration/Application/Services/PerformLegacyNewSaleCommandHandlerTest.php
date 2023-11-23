<?php
declare(strict_types=1);

namespace Tests\Integration\Application\Services;

use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlResponse;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleCommandHttpDTO;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\PerformLegacyNewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Services\LegacyService;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;
use Tests\IntegrationTestCase;

/**
 * @group legacyService
 * Class PerformLegacyNewSaleCommandHandlerTest
 * @package Tests\Integration\Application\Services
 */
class PerformLegacyNewSaleCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var PerformLegacyNewSaleCommandHandler
     */
    private $handler;

    /**
     * @var LegacyService|MockObject
     */
    private $legacyService;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        $legacyResponse = (new GeneratePurchaseUrlResponse())
            ->setTraceId('948940b2-a2e4-4583-8ba3-63b338f353ba')
            ->setSessionId('948940b2-a2e4-4583-8ba3-63b338f353ba')
            ->setCorrelationId('948940b2-a2e4-4583-8ba3-63b338f353ba')
            ->setRedirectUrl('redirectUrl');

        $response = LegacyNewSaleBillerResponse::createSuccessResponse(
            'redirectUrl',
            (string) $legacyResponse,
            null,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->legacyService = $this->createMock(LegacyService::class);
        $this->legacyService->method('chargeNewSale')->willReturn(
            $response
        );
        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $transactionRepositoryMock = $this->createMock(FirestoreTransactionRepository::class);
        /** @var BILoggerService $biLoggerMock */
        $biLoggerMock = $this->createMock(BILoggerService::class);

        parent::setUp();

        $this->handler = new PerformLegacyNewSaleCommandHandler(
            new LegacyNewSaleHttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->legacyService,
            $biLoggerMock
        );
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function legacy_handler_with_successful_execute_should_return_dto(): void
    {
        $command = $this->createPerformLegacyNewSaleCommand();
        $result  = $this->handler->execute($command);

        $this->assertInstanceOf(LegacyNewSaleCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function legacy_handler_with_successful_execute_should_add_all_transactions_to_repo(): void
    {
        $command         = $this->createPerformLegacyNewSaleCommand();
        $numberOfCharges = count($command->charges());

        $transactionRepositoryMock = $this->createMock(FirestoreTransactionRepository::class);
        $transactionRepositoryMock->expects($this->exactly($numberOfCharges))->method('add');

        $handler = new PerformLegacyNewSaleCommandHandler(
            new LegacyNewSaleHttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->legacyService,
            $this->createMock(BILoggerService::class)
        );

        $handler->execute($command);
    }
}
