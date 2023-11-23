<?php
declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommandHandler;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\CardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCompleteThreeDCreditCardAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardChargeTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardTranslationService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateNewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use ReflectionClass;
use ReflectionException;
use Tests\IntegrationTestCase;

class RocketgateCreditCardTranslationServiceTest extends IntegrationTestCase
{
    /**
     * @var RocketgateNewCreditCardChargeAdapter
     */
    private $newCardAdapter;

    /**
     * @var RocketgateExistingCreditCardChargeAdapter
     */
    private $existingCardAdapter;

    /**
     * @var RocketgateSuspendRebillAdapter
     */
    private $suspendRebillAdapter;

    /**
     * @var RocketgateCompleteThreeDCreditCardAdapter
     */
    private $completeThreeDAdapter;

    /**
     * @var RocketgateSimplifiedCompleteThreeDAdapter
     */
    private $simplifiedCompleteThreeDAdapter;

    /**
     * @var CardUploadAdapter
     */
    private $cardUploadAdapter;

    /**
     * @var ChargeTransaction
     */
    private $newCCTransaction;

    /**
     * @var ChargeTransaction
     */
    private $existingCCTransaction;

    /**
     * @var RebillUpdateTransaction
     */
    private $cancelRebillTransaction;

    /**
     * @var ChargeTransaction
     */
    private $pendingThreeDTransaction;

    /**
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->newCardAdapter                  = $this->createMock(RocketgateNewCreditCardChargeAdapter::class);
        $this->existingCardAdapter             = $this->createMock(RocketgateExistingCreditCardChargeAdapter::class);
        $this->suspendRebillAdapter            = $this->createMock(RocketgateSuspendRebillAdapter::class);
        $this->completeThreeDAdapter           = $this->createMock(CompleteThreeDAdapter::class);
        $this->simplifiedCompleteThreeDAdapter = $this->createMock(RocketgateSimplifiedCompleteThreeDAdapter::class);
        $this->cardUploadAdapter               = $this->createMock(CardUploadAdapter::class);

        $this->newCCTransaction         = $this->createPendingTransactionWithRebillForNewCreditCard();
        $this->existingCCTransaction    = $this->createPendingTransactionWithRebillForExistingCreditCard();
        $this->cancelRebillTransaction  = $this->createCancelRebillRocketgateTransaction();
        $this->pendingThreeDTransaction = $this->createPendingTransactionWithRebillForNewCreditCard(
            [
                'useThreeD' => true
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_new_credit_card_should_create_a_rocketgate_new_credit_card_charge_command(): void
    {
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateNewCreditCardChargeCommand'])
            ->getMock();
        $translationService
            ->expects($this->once())
            ->method('createRocketgateNewCreditCardChargeCommand')
            ->with($this->newCCTransaction);

        $translationService->chargeWithNewCreditCard($this->newCCTransaction);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function charge_with_existing_credit_card_should_recieve_sent_parameters_to_rocketgate(): void
    {
        $existingCreditCardHandler = $this->createMock(ChargeWithExistingCreditCardCommandHandler::class);

        /** @var RocketgateCreditCardTranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    new RocketgateExistingCreditCardChargeAdapter(
                        new ChargeClient(
                            null,
                            $existingCreditCardHandler
                        ),
                        $this->createMock(RocketgateCreditCardChargeTranslator::class)
                    ),
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(RocketgateCreditCardTranslationService::class);
        $method     = $reflection->getMethod('createRocketgateExistingCreditCardChargeCommand');
        $method->setAccessible(true);

        $expectedCommand = $method->invoke($translationService, $this->existingCCTransaction);

        $existingCreditCardHandler->expects($this->once())
            ->method('execute')->with($expectedCommand);

        $translationService->chargeWithExistingCreditCard($this->existingCCTransaction);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function charge_with_new_credit_card_should_receive_sent_parameters_to_rocketgate(): void
    {
        $newCreditCardHandler = $this->createMock(ChargeWithNewCreditCardCommandHandler::class);

        /** @var RocketgateCreditCardTranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    new RocketgateNewCreditCardChargeAdapter(
                        new ChargeClient(
                            $newCreditCardHandler,
                            null
                        ),
                        $this->createMock(RocketgateCreditCardChargeTranslator::class)
                    ),
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(RocketgateCreditCardTranslationService::class);
        $method     = $reflection->getMethod('createRocketgateNewCreditCardChargeCommand');
        $method->setAccessible(true);

        $expectedCommand = $method->invoke($translationService, $this->newCCTransaction);

        $newCreditCardHandler->expects($this->once())
            ->method('execute')->with($expectedCommand);

        $translationService->chargeWithNewCreditCard($this->newCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_new_credit_card_should_call_the_adapter_charge_method(): void
    {
        $this->newCardAdapter->expects($this->once())->method('charge');

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateNewCreditCardChargeCommand'])
            ->getMock();

        $translationService->chargeWithNewCreditCard($this->newCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_existing_credit_card_should_create_a_rocketgate_existing_credit_card_charge_command(): void
    {
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateExistingCreditCardChargeCommand'])
            ->getMock();
        $translationService
            ->expects($this->once())
            ->method('createRocketgateExistingCreditCardChargeCommand')
            ->with($this->existingCCTransaction);

        $translationService->chargeWithExistingCreditCard($this->existingCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_existing_credit_card_should_call_the_adapter_charge_method(): void
    {
        $this->existingCardAdapter->expects($this->once())->method('charge');

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateExistingCreditCardChargeCommand'])
            ->getMock();

        $translationService->chargeWithExistingCreditCard($this->existingCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_existing_credit_card_should_throw_exception_for_invalid_payment_information(): void
    {
        $this->expectException(InvalidPaymentInformationException::class);

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateExistingCreditCardChargeCommand'])
            ->getMock();

        $translationService->chargeWithNewCreditCard($this->existingCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function charge_with_new_credit_card_should_throw_exception_for_invalid_payment_information(): void
    {
        $this->expectException(InvalidPaymentInformationException::class);

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateNewCreditCardChargeCommand'])
            ->getMock();

        $translationService->chargeWithExistingCreditCard($this->newCCTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function suspend_rebill_should_create_a_rocketgate_suspend_rebill_command(): void
    {
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateSuspendRebillCommand'])
            ->getMock();
        $translationService
            ->expects($this->once())
            ->method('createRocketgateSuspendRebillCommand')
            ->with($this->cancelRebillTransaction);

        $translationService->suspendRebill($this->cancelRebillTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function suspend_rebill_should_call_the_adapter_suspend_method(): void
    {
        $this->suspendRebillAdapter->expects($this->once())->method('suspend');

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateSuspendRebillCommand'])
            ->getMock();

        $translationService->suspendRebill($this->cancelRebillTransaction);
    }

    /**
     * @test
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public function suspend_rebill_should_receive_sent_parameters_to_rocketgate(): void
    {
        $suspendRebillHandler = $this->createMock(SuspendRebillCommandHandler::class);

        /** @var RocketgateCreditCardTranslationService|MockObject $translationService */
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    new RocketgateSuspendRebillAdapter(
                        new ChargeClient(
                            null,
                            null,
                            $suspendRebillHandler
                        ),
                        $this->createMock(RocketgateCreditCardChargeTranslator::class)
                    ),
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods([])
            ->getMock();

        $reflection = new ReflectionClass(RocketgateCreditCardTranslationService::class);
        $method     = $reflection->getMethod('createRocketgateSuspendRebillCommand');
        $method->setAccessible(true);

        $expectedCommand = $method->invoke($translationService, $this->cancelRebillTransaction);

        $suspendRebillHandler->expects($this->once())
            ->method('execute')->with($expectedCommand);

        $translationService->suspendRebill($this->cancelRebillTransaction);
    }

    /**
     * @test
     * @return void
     */
    public function complete_threeD_should_create_a_rocketgate_complete_threeD_command_with_pares_parameter(): void
    {
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateCompleteThreeDCommand'])
            ->getMock();
        $translationService
            ->expects($this->once())
            ->method('createRocketgateCompleteThreeDCommand')
            ->with($this->pendingThreeDTransaction, 'SimulatedPARES10001000E00B000');

        $translationService->completeThreeDCreditCard(
            $this->pendingThreeDTransaction,
            'SimulatedPARES10001000E00B000',
            null,
            null
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_should_create_a_rocketgate_complete_threeD_command_with_md_parameter(): void
    {
        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateCompleteThreeDCommand'])
            ->getMock();
        $translationService
            ->expects($this->once())
            ->method('createRocketgateCompleteThreeDCommand')
            ->with($this->pendingThreeDTransaction, null);

        $translationService->completeThreeDCreditCard(
            $this->pendingThreeDTransaction,
            null,
            '10001732904A027',
            null
        );
    }

    /**
     * @test
     * @return void
     */
    public function complete_threeD_should_call_the_adapter_complete_method(): void
    {
        $this->completeThreeDAdapter->expects($this->once())->method('complete');

        $translationService = $this->getMockBuilder(RocketgateCreditCardTranslationService::class)
            ->setConstructorArgs(
                [
                    $this->existingCardAdapter,
                    $this->newCardAdapter,
                    $this->suspendRebillAdapter,
                    $this->completeThreeDAdapter,
                    $this->simplifiedCompleteThreeDAdapter,
                    $this->cardUploadAdapter
                ]
            )
            ->onlyMethods(['createRocketgateCompleteThreeDCommand'])
            ->getMock();

        $translationService->completeThreeDCreditCard(
            $this->pendingThreeDTransaction,
            'SimulatedPARES10001000E00B000',
            null,
            null
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws ReflectionException
     */
    public function charge_with_existing_credit_card_command_should_contain_isMerchantInitiated_to_true(): void
    {
        $translationService = new RocketgateCreditCardTranslationService(
            $this->existingCardAdapter,
            $this->newCardAdapter,
            $this->suspendRebillAdapter,
            $this->completeThreeDAdapter,
            $this->simplifiedCompleteThreeDAdapter,
            $this->cardUploadAdapter
        );

        $reflection = new ReflectionClass($translationService);
        $method     = $reflection->getMethod('createRocketgateExistingCreditCardChargeCommand');
        $method->setAccessible(true);

        /**
         * @var ChargeWithExistingCreditCardCommand $result
         */
        $result = $method->invokeArgs(
            $translationService,
            [
                $this->createPendingTransactionWithRebillForExistingCreditCard(
                    [
                        'siteId' => '299d3e6b-cf3d-11e9-8c91-0cc47a283dd2',
                        'simplified3DS' => false
                    ]
                )
            ]
        );

        $optionalBillerFields = $result->optionalBillerFields();

        self::assertTrue($optionalBillerFields['isMerchantInitiated']);
    }


    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws ReflectionException
     */
    public function charge_with_existing_credit_card_command_should_contain_isMerchantInitiated_to_false(): void
    {
        $translationService = new RocketgateCreditCardTranslationService(
            $this->existingCardAdapter,
            $this->newCardAdapter,
            $this->suspendRebillAdapter,
            $this->completeThreeDAdapter,
            $this->simplifiedCompleteThreeDAdapter,
            $this->cardUploadAdapter
        );

        $reflection = new ReflectionClass($translationService);
        $method     = $reflection->getMethod('createRocketgateExistingCreditCardChargeCommand');
        $method->setAccessible(true);

        /**
         * @var ChargeWithExistingCreditCardCommand $result
         */
        $result = $method->invokeArgs(
            $translationService,
            [
                $this->createPendingTransactionWithRebillForExistingCreditCard(
                    [
                        'siteId' => '299d3e6b-cf3d-11e9-8c91-0cc47a283dd2',
                        'simplified3DS' => true
                    ]
                )
            ]
        );

        $optionalBillerFields = $result->optionalBillerFields();

        self::assertFalse($optionalBillerFields['isMerchantInitiated']);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws ReflectionException
     */
    public function charge_with_existing_credit_card_command_should_contain_isMerchantInitiated_set_to_false(): void
    {
        $translationService = new RocketgateCreditCardTranslationService(
            $this->existingCardAdapter,
            $this->newCardAdapter,
            $this->suspendRebillAdapter,
            $this->completeThreeDAdapter,
            $this->simplifiedCompleteThreeDAdapter,
            $this->cardUploadAdapter
        );

        $reflection = new ReflectionClass($translationService);
        $method     = $reflection->getMethod('createRocketgateExistingCreditCardChargeCommand');
        $method->setAccessible(true);

        /**
         * @var ChargeWithExistingCreditCardCommand $result
         */
        $result = $method->invokeArgs(
            $translationService,
            [$this->createPendingTransactionWithRebillForExistingCreditCard(['siteId' => $this->faker->uuid])]
        );

        $optionalBillerFields = $result->optionalBillerFields();

        $this->assertFalse($optionalBillerFields['isMerchantInitiated']);
    }
}
