<?php

namespace Tests\Integration\Application\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\BI\BILoggerService;
use ProBillerNG\Pumapay\Application\Services\GenerateQrCodeCommandHandler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayQrCodeHttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\PumapayQrCodeTransactionService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayRetrieveQrCodeBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayPostbackAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayRetrieveQrCodeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\IntegrationTestCase;

class RetrievePumapayQrCodeCommandHandlerTest extends IntegrationTestCase
{
    /**
     * @var RetrievePumapayQrCodeCommandHandler
     */
    private $handler;

    /**
     * @var MockObject
     */
    private $repository;

    /**
     * @var MockObject
     */
    private $retrieveQrCodeService;

    /**
     * @var MockObject|BILoggerService
     */
    protected $biService;

    /**
     * @var MockObject
     */
    private $pumapayTransactionService;

    /**
     * Setup test
     * @return void
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository                = $this->createMock(TransactionRepository::class);
        $this->retrieveQrCodeService     = $this->createMock(PumapayTranslatingService::class);
        $this->biService                 = $this->createMock(BILoggerService::class);
        $this->pumapayTransactionService = new PumapayQrCodeTransactionService($this->repository);

        $billerResponse = PumapayRetrieveQrCodeBillerResponse::create(
            new \DateTimeImmutable(),
            '{ 
               "request":{ 
                  "currency":"EUR",
                  "title":"Brazzers Membership",
                  "description":"1$ day then daily rebill at 1$ for 3 days",
                  "frequency":null,
                  "trialPeriod":null,
                  "numberOfPayments":null,
                  "typeID":2,
                  "amount":100,
                  "initialPaymentAmount":null
               },
               "response":{ 
                  "success":true,
                  "status":200,
                  "message":"Successfully retrieved the QR code.",
                  "data":{
                     "qrImage": "qrCode",
                     "encryptText": "encryptText"
                  }
               },
               "code":200,
               "reason":null
            }',
            new \DateTimeImmutable()
        );

        $this->retrieveQrCodeService->method('retrieveQrCode')->willReturn($billerResponse);

        $this->handler = new RetrievePumapayQrCodeCommandHandler(
            $this->repository,
            new PumapayQrCodeHttpCommandDTOAssembler(),
            $this->retrieveQrCodeService,
            $this->biService,
            $this->pumapayTransactionService
        );
    }

    /**
     * @test
     * @return array
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_return_a_dto_when_a_valid_command_is_provided(): array
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
            '1$ day then daily rebill at 1$ for 3 days'
        );

        $result = $this->handler->execute($command);

        $this->assertInstanceOf(RetrieveQrCodeCommandHttpDTO::class, $result);

        return $result->jsonSerialize();
    }

    /**
     * @test
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     * @param array $result Result
     * @return void
     */
    public function it_should_have_a_transaction_id_on_response(array $result): void
    {
        $this->assertArrayHasKey('transactionId', $result);
    }

    /**
     * @test
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     * @param array $result Result
     * @return void
     */
    public function it_should_have_a_status_on_response(array $result): void
    {
        $this->assertArrayHasKey('status', $result);
    }

    /**
     * @test
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     * @param array $result Result
     * @return void
     */
    public function it_should_have_a_qr_code_on_response(array $result): void
    {
        $this->assertArrayHasKey('qrCode', $result);
    }

    /**
     * @test
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     * @param array $result Result
     * @return void
     */
    public function it_should_have_an_encrypt_text_on_response(array $result): void
    {
        $this->assertArrayHasKey('encryptText', $result);
    }

    /**
     * @test
     * @depends it_should_return_a_dto_when_a_valid_command_is_provided
     * @param array $result Result
     * @return void
     */
    public function it_should_have_a_pending_status_on_response(array $result): void
    {
        $this->assertEquals('pending', $result['status']);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_throw_exception_when_invalid_command_given(): void
    {
        $this->expectException(\TypeError::class);

        $this->handler->execute(new \stdClass());
    }


    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_throw_invalid_charge_information_exception_when_amount_is_negative(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            -1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );

        $this->handler->execute($command);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_have_an_aborted_transaction_when_biller_response_fails(): void
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
            '1$ day then daily rebill at 1$ for 3 days'
        );

        $retrieveQrCodeService = $this->createMock(PumapayTranslatingService::class);

        $billerResponse = $this->createMock(PumapayRetrieveQrCodeBillerResponse::class);
        $billerResponse->method('aborted')->willReturn(true);
        $retrieveQrCodeService->method('retrieveQrCode')->willReturn($billerResponse);

        $handler = new RetrievePumapayQrCodeCommandHandler(
            $this->repository,
            new PumapayQrCodeHttpCommandDTOAssembler(),
            $retrieveQrCodeService,
            $this->biService,
            $this->pumapayTransactionService
        );

        $result = $handler->execute($command);

        $this->assertEquals('aborted', $result->jsonSerialize()['status']);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     */
    public function it_should_write_the_bi_event(): void
    {
        $this->biService->expects($this->once())->method('write');

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );

        $retrieveQrCodeService = $this->createMock(PumapayTranslatingService::class);

        $billerResponse = $this->createMock(PumapayRetrieveQrCodeBillerResponse::class);
        $retrieveQrCodeService->method('retrieveQrCode')->willReturn($billerResponse);

        $handler = new RetrievePumapayQrCodeCommandHandler(
            $this->repository,
            new PumapayQrCodeHttpCommandDTOAssembler(),
            $retrieveQrCodeService,
            $this->biService,
            $this->pumapayTransactionService
        );

        $handler->execute($command);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @return void
     */
    public function it_should_stop_execution_and_handle_exception_when_biller_response_is_invalid(): void
    {
        $this->expectException(InvalidBillerResponseException::class);

        $qrCommandHandler = $this->createMock(GenerateQrCodeCommandHandler::class);
        $qrCommandHandler->method('execute')->willReturn(
            json_encode(
                [
                    'code'   => '530',
                    'reason' => 'Server error: `GET https://psp-backend.pumapay.io/api/v2/api-key-auth/qr/pull-payment/BHFJ5epZgSHTihLrgsZpYSJkFjqKb3JC/im5oXFrmXRzpekY2vl8m2C6AR2e12H1A/6b1f4794-aa13-4680-b839-49c6ccc49d02?currency=USD&title=PornhubPremium_9.99_30_9.99_30_Crypto&description=Membership%20to%20pornhubpremium.com%20for%2030%20days%20for%20a%20charge%20of%20%249.99&frequency=2592000&trialPeriod=2592000&numberOfPayments=60&typeID=6&amount=999&initialPaymentAmount=999` resulted in a `530 ` response'
                ]
            )
        );

        $pumapayRetrieveQrCodeAdapter = new PumapayRetrieveQrCodeAdapter(
            $qrCommandHandler,
            new PumapayTranslator()
        );

        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );

        $retrieveQrCodeService = new PumapayTranslatingService(
            $pumapayRetrieveQrCodeAdapter,
            $this->createMock(PumapayPostbackAdapter::class),
            $this->createMock(PumapayCancelRebillAdapter::class)
        );

        $handler = new RetrievePumapayQrCodeCommandHandler(
            $this->repository,
            new PumapayQrCodeHttpCommandDTOAssembler(),
            $retrieveQrCodeService,
            $this->biService,
            $this->pumapayTransactionService
        );

        $handler->execute($command);
    }
}
