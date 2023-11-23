<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Service;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\Aborted;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Declined;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayQrCodeTransactionService;
use Tests\UnitTestCase;

class PumapayQrCodeTransactionServiceTest extends UnitTestCase
{
    /** @var MockObject|TransactionRepository */
    protected $repository;

    /**
     * @var PumapayQrCodeTransactionService
     */
    private $pumapayTransactionService;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository                = $this->createMock(TransactionRepository::class);
        $this->pumapayTransactionService = new PumapayQrCodeTransactionService($this->repository);
    }

    /**
     * @test
     * @return void
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws TransactionAlreadyProcessedException
     */
    public function it_should_throws_exception_when_transaction_given_is_not_found(): void
    {
        $this->expectException(PreviousTransactionNotFoundException::class);

        $this->repository->method('findById')->willReturn(null);

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $this->faker->uuid
        );

        $this->pumapayTransactionService->createOrUpdateTransaction($command);
    }

    /**
     * @test
     * @param string $billerName Biller Name.
     * @return void
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws TransactionAlreadyProcessedException
     * @dataProvider returnInvalidBillerName
     */
    public function it_should_throws_exception_when_transaction_given_not_from_pumapay(string $billerName): void
    {
        $this->expectException(InvalidBillerNameException::class);

        $mockedSettings = $this->createMock(BillerSettings::class);
        $mockedSettings->method('billerName')->willReturn($billerName);

        $mocekdTransaction = $this->createMock(ChargeTransaction::class);
        $mocekdTransaction->method('billerChargeSettings')->willReturn($mockedSettings);

        $this->repository->method('findById')->willReturn($mocekdTransaction);

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $this->faker->uuid
        );

        $this->pumapayTransactionService->createOrUpdateTransaction($command);
    }

    /**
     * @return array
     */
    public function returnInvalidBillerName(): array
    {
        return [
            'empty' => [''],
            'epoch' => ['epoch'],
        ];
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws TransactionAlreadyProcessedException
     */
    public function it_should_update_transaction_when_trasacion_id_is_provided(): void
    {
        $transaction   = $this->createChargeTransactionWithoutRebillOnPumapay();
        $transactionId = (string) $transaction->transactionId();
        $this->repository->method('findById')->willReturn($transaction);

        $updatedSiteId = $this->faker->uuid;

        $command = new RetrievePumapayQrCodeCommand(
            $updatedSiteId,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $transactionId
        );

        $updatedTransaction = $this->pumapayTransactionService->createOrUpdateTransaction($command);

        $this->assertEquals($updatedSiteId, (string) $updatedTransaction->siteId());
        $this->assertEquals($transactionId, (string) $updatedTransaction->transactionId());
    }

    /**
     * @test
     * @param string $billerName Biller Name.
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws PreviousTransactionNotFoundException
     * @throws TransactionAlreadyProcessedException
     * @dataProvider returnValidBillerNamesForQrCodeRetrieve
     */
    public function it_should_update_transaction_when_trasacion_id_is_provided_from_new_sale_endpoint(string $billerName): void
    {
        $data['billerName'] = $billerName;
        $transaction        = $this->createPendingLegacyTransaction($data);
        $transactionId      = (string) $transaction->transactionId();

        $this->repository->method('findById')->willReturn($transaction);

        $updatedSiteId = $this->faker->uuid;

        $command = new RetrievePumapayQrCodeCommand(
            $updatedSiteId,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $transactionId
        );

        $updatedTransaction = $this->pumapayTransactionService->createOrUpdateTransaction($command);

        $this->assertEquals($updatedSiteId, (string) $updatedTransaction->siteId());
        $this->assertEquals($transactionId, (string) $updatedTransaction->transactionId());
    }

    /**
     * @return array
     */
    public function returnValidBillerNamesForQrCodeRetrieve(): array
    {
        return [
            'first_capital_letter' => ['Pumapay'],
            'no_capital_letter'    => ['pumapay'],
            'all_capital_letters'  => ['PUMAPAY'],
            'crypto'               => ['crypto'],
        ];
    }

    /**
     * @test
     * @return void
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws TransactionAlreadyProcessedException
     */
    public function it_should_create_transaction_when_no_trasacion_id_is_provided(): void
    {
        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            null
        );

        $transaction = $this->pumapayTransactionService->createOrUpdateTransaction($command);

        $this->assertInstanceOf(ChargeTransaction::class, $transaction);
    }

    /**
     * @test
     * @param AbstractStatus $status Transaction Status
     * @return void
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws TransactionAlreadyProcessedException
     * @dataProvider returnProcessedStatus
     */
    public function it_should_not_update_the_transaction_when_transaction_is_already_processed(AbstractStatus $status): void
    {
        $this->expectException(TransactionAlreadyProcessedException::class);

        $mockedSettings = $this->createMock(BillerSettings::class);
        $mockedSettings->method('billerName')->willReturn('pumapay');

        $mockedTransaction = $this->createMock(ChargeTransaction::class);
        $mockedTransaction->method('billerChargeSettings')->willReturn($mockedSettings);
        $mockedTransaction->method('status')->willReturn($status);

        $this->repository->method('findById')->willReturn($mockedTransaction);

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days',
            $this->faker->uuid
        );

        $this->pumapayTransactionService->createOrUpdateTransaction($command);
    }

    /**
     * @return array
     */
    public function returnProcessedStatus(): array
    {
        return [
            'Approved' => [Approved::create()],
            'Aborted'  => [Aborted::create()],
            'Declined' => [Declined::create()]
        ];
    }
}
