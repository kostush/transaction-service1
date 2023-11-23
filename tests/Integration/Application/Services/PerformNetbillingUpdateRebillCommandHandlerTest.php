<?php
namespace Tests\Integration\Application\Services;

use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\TransactionCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\UpdateRebillService;
use ProBillerNG\Transaction\Infrastructure\Domain\Repository\FirestoreTransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillTranslatingService;
use Tests\IntegrationTestCase;

class PerformNetbillingUpdateRebillCommandHandlerTest extends IntegrationTestCase
{
    private $handler;

    private $updateRebillService;

    private $rebill;

    private $payment;

    /**
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->rebill = [
            'amount'    => 20,
            'start'     => 365,
            'frequency' => 365,
        ];

        $this->payment = new Payment(
            'cc',
            new ExistingCreditCardInformation(
                $_ENV['NETBILLING_CARD_HASH']
            )
        );

        $this->updateRebillService = $this->createMock(NetbillingUpdateRebillTranslatingService::class);
        $this->updateRebillService->method('update')->willReturn(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => $this->getBillerRequest(),
                        'response' => $this->getBillerResponse(),
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );

        $previousTransaction = ChargeTransaction::createWithRebillOnNetbilling(
            $this->faker->uuid,
            $this->faker->randomFloat(2, 1, 100),
            NetbillingBillerSettings::NETBILLING,
            'USD',
            new Payment(
                'cc',
                new NewCreditCardInformation(
                    $this->faker->creditCardNumber('Visa'),
                    (string) $this->faker->numberBetween(1, 12),
                    $this->faker->numberBetween(2025, 2030),
                    (string) $this->faker->numberBetween(100, 999),
                    new Member(
                        $this->faker->name,
                        $this->faker->lastName,
                        $this->faker->userName,
                        $this->faker->email,
                        $this->faker->phoneNumber,
                        $this->faker->address,
                        $this->faker->postcode,
                        $this->faker->city,
                        'state',
                        'country'
                    )
                ),
            ),
            new NetbillingChargeSettings(
                $_ENV['NETBILLING_SITE_TAG_2'],
                $_ENV['NETBILLING_ACCOUNT_ID_2'],
                $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                30,
                null,
                null,
                null,
                null,
                null,
                "114023189488"
            ),
            new Rebill(
                $this->faker->randomFloat(2, 1, 100),
                $this->faker->numberBetween(1, 100),
                $this->faker->numberBetween(1, 100)
            )
        );

        /** @var FirestoreTransactionRepository $transactionRepositoryMock */
        $transactionRepositoryMock = $this->createMock(FirestoreTransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn($previousTransaction);

        /** @var BILoggerService $biLoggerMock */
        $biLoggerMock = $this->createMock(BILoggerService::class);

        $this->handler = new PerformNetbillingUpdateRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->updateRebillService,
            $biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidPayloadException
     * @throws TransactionCreationException
     * @throws TransactionNotFoundException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided()
    {
        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'currency'         => "USD",
                'rebill'           => $this->rebill,
                'payment'          => $this->payment,
            ]
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(TransactionCommandHttpDTO::class, $result);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_missing_transaction_exception_when_id_is_not_provided()
    {
        $this->expectException(MissingTransactionInformationException::class);

        // Invalid command - no transaction id
        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'transactionId'    => '',
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'currency'         => "USD",
                'rebill'           => $this->rebill,
                'payment'          => $this->payment,
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @throws TransactionCreationException
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_transaction_not_found()
    {
        $this->expectException(TransactionNotFoundException::class);

        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);

        $transactionRepositoryMock->method('findById')->willReturn(null);

        $biLoggerMock = $this->createMock(BILoggerService::class);

        $this->handler = new PerformNetbillingUpdateRebillCommandHandler(
            new HttpCommandDTOAssembler(),
            $transactionRepositoryMock,
            $this->updateRebillService,
            $biLoggerMock,
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'currency'         => "USD",
                'rebill'           => $this->rebill,
                'payment'          => $this->payment,
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidPayloadException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws TransactionCreationException
     * @throws TransactionNotFoundException
     */
    public function it_should_throw_charge_exception_when_amount_with_invalid_payment_information_is_provided()
    {
        $this->expectException(InvalidCreditCardInformationException::class);

        $this->payment = new Payment(
            'cc',
            new ExistingCreditCardInformation(
                'invalid'
            )
        );

        $command = $this->createPerformNetbillingUpdateRebillCommand(
            [
                'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                'amount'           => 20,
                'currency'         => "USD",
                'rebill'           => $this->rebill,
                'payment'          => $this->payment,
            ]
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_non_netbilling_transaction_is_provided()
    {
        // GIVEN
        $transactionRepositoryMock = $this->createMock(TransactionRepository::class);
        $transactionRepositoryMock->method('findById')->willReturn($this->createSomeNonNetbillingTransaction());

        $performNetbillingUpdateRebillCommandMock = $this->createMock(PerformNetbillingUpdateRebillCommand::class);
        $performNetbillingUpdateRebillCommandMock->method('transactionId')->willReturn($this->faker->uuid);

        $handler = new PerformNetbillingUpdateRebillCommandHandler(
            $this->createMock(HttpCommandDTOAssembler::class),
            $transactionRepositoryMock,
            $this->createMock(UpdateRebillService::class),
            $this->createMock(BILoggerService::class),
            $this->createMock(DeclinedBillerResponseExtraDataRepository::class)
        );

        // THEN
        $this->expectException(InvalidTransactionInformationException::class);

        // WHEN
        $handler->execute($performNetbillingUpdateRebillCommandMock);
    }

    /**
     * @return Transaction
     */
    private function createSomeNonNetbillingTransaction(): Transaction
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->method('billerName')->willReturn('any-biller-name-but-netbilling');
        return $transactionMock;
    }
}
