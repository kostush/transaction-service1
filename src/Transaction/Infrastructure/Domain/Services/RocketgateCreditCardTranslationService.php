<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Application\Services\BillerResponseAttributeExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\CreditCardCharge;
use ProBillerNG\Transaction\Infrastructure\Domain\CardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\ExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\NewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\SimplifiedCompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\SuspendRebillAdapter;

class RocketgateCreditCardTranslationService implements CreditCardCharge
{
    use BillerResponseAttributeExtractorTrait;

    /**
     * @var ExistingCreditCardChargeAdapter
     */
    protected $existingCreditCardAdapter;

    /**
     * @var NewCreditCardChargeAdapter
     */
    protected $newCreditCardAdapter;

    /**
     * @var SuspendRebillAdapter
     */
    protected $suspendRebillAdapter;

    /**
     * @var CompleteThreeDAdapter
     */
    protected $completeThreeDAdapter;

    /**
     * @var SimplifiedCompleteThreeDAdapter
     */
    protected $simplifiedCompleteThreeDAdapter;

    /**
     * @var CardUploadAdapter
     */
    protected $cardUploadAdapter;

    /**
     * RocketgateTranslationService constructor.
     * @param ExistingCreditCardChargeAdapter $existingCreditCardAdapter       Existing cc adapter
     * @param NewCreditCardChargeAdapter      $newCreditCardChargeAdapter      New cc adapter
     * @param SuspendRebillAdapter            $suspendRebillAdapter            Suspend Rebill Adapter
     * @param CompleteThreeDAdapter           $completeThreeDAdapter           Complete ThreeD Adapter
     * @param SimplifiedCompleteThreeDAdapter $simplifiedCompleteThreeDAdapter Simplified Complete ThreeD Adapter
     * @param CardUploadAdapter               $cardUploadAdapter               Card upload Adapter
     */
    public function __construct(
        ExistingCreditCardChargeAdapter $existingCreditCardAdapter,
        NewCreditCardChargeAdapter $newCreditCardChargeAdapter,
        SuspendRebillAdapter $suspendRebillAdapter,
        CompleteThreeDAdapter $completeThreeDAdapter,
        SimplifiedCompleteThreeDAdapter $simplifiedCompleteThreeDAdapter,
        CardUploadAdapter $cardUploadAdapter
    ) {
        $this->existingCreditCardAdapter       = $existingCreditCardAdapter;
        $this->newCreditCardAdapter            = $newCreditCardChargeAdapter;
        $this->suspendRebillAdapter            = $suspendRebillAdapter;
        $this->completeThreeDAdapter           = $completeThreeDAdapter;
        $this->simplifiedCompleteThreeDAdapter = $simplifiedCompleteThreeDAdapter;
        $this->cardUploadAdapter               = $cardUploadAdapter;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function chargeWithNewCreditCard(ChargeTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate new credit card charge request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateChargeCommand = $this->createRocketgateNewCreditCardChargeCommand($transaction);

        return $this->newCreditCardAdapter->charge($rocketgateChargeCommand, new DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function chargeWithExistingCreditCard(ChargeTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate existing credit card charge request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateChargeCommand = $this->createRocketgateExistingCreditCardChargeCommand($transaction);

        return $this->existingCreditCardAdapter->charge($rocketgateChargeCommand, new DateTimeImmutable());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     *
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function suspendRebill(RebillUpdateTransaction $transaction): RocketgateCreditCardBillerResponse
    {
        Log::info(
            'Preparing Rocketgate suspend rebill request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateSuspendRebillCommand = $this->createRocketgateSuspendRebillCommand($transaction);

        return $this->suspendRebillAdapter->suspend($rocketgateSuspendRebillCommand, new DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction Charge Transaction
     * @param string|null       $pares       Pares
     * @param string|null       $md          Biller transaction id
     * @param string|null       $cvv         CVV retrieved from Redis
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function completeThreeDCreditCard(
        ChargeTransaction $transaction,
        ?string $pares,
        ?string $md,
        ?string $cvv
    ): RocketgateCreditCardBillerResponse {
        Log::info(
            'Preparing Rocketgate complete threeD request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $rocketgateCompleteThreeDCommand = $this->createRocketgateCompleteThreeDCommand(
            $transaction,
            $pares,
            $md,
            $cvv
        );

        return $this->completeThreeDAdapter->complete($rocketgateCompleteThreeDCommand, new DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $queryString Query string
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function simplifiedCompleteThreeD(
        ChargeTransaction $transaction,
        string $queryString
    ): RocketgateCreditCardBillerResponse {
        Log::info(
            'Preparing Rocketgate simplified complete threeD request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        $command = $this->createRocketgateSimplifiedCompleteThreeDCommand($transaction, $queryString);

        return $this->simplifiedCompleteThreeDAdapter->simplifiedComplete($command, new DateTimeImmutable());
    }

    /**
     * @param ChargeTransaction $transaction Charge Transaction
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     * @throws \Exception
     */
    public function cardUpload(
        ChargeTransaction $transaction
    ): RocketgateCreditCardBillerResponse {
        Log::info(
            'Preparing Rocketgate card upload request',
            ['transactionId' => (string) $transaction->transactionId()]
        );

        return $this->cardUploadAdapter->cardUpload(
            $this->createRocketgateCardUploadCommand($transaction),
            new DateTimeImmutable()
        );
    }

    /**
     * @param ChargeTransaction $transaction The transaction object
     * @return CardUploadCommand
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    protected function createRocketgateCardUploadCommand(
        ChargeTransaction $transaction
    ): CardUploadCommand {
        $mandatoryFields       = $this->prepareCommonMandatoryFields($transaction);
        $optionalRebillDetails = $this->prepareOptionalRebillDetails($transaction);
        $optionalBillerDetails = $this->prepareOptionalBillerFields($transaction);

        if ($transaction->paymentInformation() instanceof CreditCardInformation) {
            return new CardUploadCommand(
                (string) $transaction->transactionId(),
                array_merge(
                    $mandatoryFields,
                    $this->prepareMandatoryFieldsForNewCreditCard($transaction)
                ),
                env("BILLER_ROCKETGATE_TEST_MODE") ?? true,
                $optionalRebillDetails,
                $this->prepareOptionalCustomerDetails($transaction),
                $optionalBillerDetails,
                $transaction->with3D()
            );
        }
        throw new InvalidPaymentInformationException();
    }

    /**
     * @param ChargeTransaction $transaction The transaction object
     * @return ChargeWithNewCreditCardCommand
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    protected function createRocketgateNewCreditCardChargeCommand(
        ChargeTransaction $transaction
    ): ChargeWithNewCreditCardCommand {
        $mandatoryFields       = $this->prepareCommonMandatoryFields($transaction);
        $optionalRebillDetails = $this->prepareOptionalRebillDetails($transaction);
        $optionalBillerDetails = $this->prepareOptionalBillerFields($transaction);

        if ($transaction->paymentInformation() instanceof CreditCardInformation) {
            return new ChargeWithNewCreditCardCommand(
                (string) $transaction->transactionId(),
                array_merge(
                    $mandatoryFields,
                    $this->prepareMandatoryFieldsForNewCreditCard($transaction)
                ),
                env("BILLER_ROCKETGATE_TEST_MODE") ?? true,
                $optionalRebillDetails,
                $this->prepareOptionalCustomerDetails($transaction),
                $optionalBillerDetails,
                $transaction->with3D(),
                $transaction->billerChargeSettings()->simplified3DS(),
                $transaction->returnUrl()
            );
        }
        throw new InvalidPaymentInformationException();
    }

    /**
     * @param ChargeTransaction $transaction The transaction object
     * @return ChargeWithExistingCreditCardCommand
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    protected function createRocketgateExistingCreditCardChargeCommand(
        ChargeTransaction $transaction
    ): ChargeWithExistingCreditCardCommand {
        $mandatoryFields       = $this->prepareCommonMandatoryFields($transaction);
        $optionalRebillDetails = $this->prepareOptionalRebillDetails($transaction);
        $optionalBillerDetails = $this->prepareOptionalBillerFields($transaction);

        // isMerchantInitiated flag is dependent of whether the site belongs to paysites
        // and also of the state of the simplified 3ds feature flag
        $optionalBillerDetails['isMerchantInitiated'] = Paysites::checkIfPaysites((string) $transaction->siteId()) &&
                                                        !$transaction->billerChargeSettings()->simplified3DS();

        if ($transaction->paymentInformation() instanceof PaymentTemplateInformation) {
            return new ChargeWithExistingCreditCardCommand(
                (string) $transaction->transactionId(),
                array_merge(
                    $mandatoryFields,
                    $this->prepareMandatoryFieldsForExistingCreditCard($transaction)
                ),
                env("BILLER_ROCKETGATE_TEST_MODE") ?? true,
                $optionalRebillDetails,
                $optionalBillerDetails,
                $transaction->with3D(),
                $transaction->billerChargeSettings()->simplified3DS(),
                $transaction->returnUrl()
            );
        }

        throw new InvalidPaymentInformationException();
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return SuspendRebillCommand
     */
    protected function createRocketgateSuspendRebillCommand(
        RebillUpdateTransaction $transaction
    ): SuspendRebillCommand {
        return new SuspendRebillCommand(
            $transaction->billerChargeSettings()->merchantId(),
            $transaction->billerChargeSettings()->merchantPassword(),
            env('BILLER_ROCKETGATE_TEST_MODE') ?? true,
            $transaction->billerChargeSettings()->merchantCustomerId(),
            $transaction->billerChargeSettings()->merchantInvoiceId()
        );
    }

    /**
     * @param ChargeTransaction $transaction Charge transaction
     * @param string|null       $pares       Pares
     * @param string|null       $md          Rocketgate biller transaction id
     * @param string|null       $cvv         CVV retrieved from Redis
     * @return CompleteThreeDCreditCardCommand
     */
    protected function createRocketgateCompleteThreeDCommand(
        ChargeTransaction $transaction,
        ?string $pares,
        ?string $md,
        ?string $cvv
    ): CompleteThreeDCreditCardCommand {

        $billerFields = $this->prepareOptionalBillerFields($transaction) + [
                'merchantId'       => $transaction->billerChargeSettings()->merchantId(),
                'merchantPassword' => $transaction->billerChargeSettings()->merchantPassword()
            ];

        // TODO done to align with the new TS
        $subsequentOperationFields = $transaction->subsequentOperationFieldsToArray();

        if ($subsequentOperationFields !== null) {
            if (empty($billerFields['merchantCustomerId']) && !empty($subsequentOperationFields['rocketgate']['merchantCustomerId'])) {
                $billerFields['merchantCustomerId'] = $subsequentOperationFields['rocketgate']['merchantCustomerId'];
            }

            if (empty($billerFields['merchantInvoiceId']) && !empty($subsequentOperationFields['rocketgate']['merchantInvoiceId'])) {
                $billerFields['merchantInvoiceId'] = $subsequentOperationFields['rocketgate']['merchantInvoiceId'];
            }
        }

        return new CompleteThreeDCreditCardCommand(
            (string) $transaction->transactionId(),
            $md ?? (string) $this->getAttribute($transaction, 'guidNo'),
            $pares,
            $billerFields,
            [
                'amount'   => $transaction->chargeInformation()->amount()->value(),
                'currency' => $transaction->chargeInformation()->currency()->code(),
            ],
            $this->prepareOptionalRebillDetails($transaction),
            $cvv,
            env("BILLER_ROCKETGATE_TEST_MODE") ?? true
        );
    }

    /**
     * @param ChargeTransaction $transaction Charge transaction
     * @param string            $queryString Query string
     * @return SimplifiedCompleteThreeDCommand
     */
    protected function createRocketgateSimplifiedCompleteThreeDCommand(
        ChargeTransaction $transaction,
        string $queryString
    ): SimplifiedCompleteThreeDCommand {
        $billerFields = [
            'merchantId'       => $transaction->billerChargeSettings()->merchantId(),
            'merchantPassword' => $transaction->billerChargeSettings()->merchantPassword(),
            'sharedSecret'     => $transaction->billerChargeSettings()->sharedSecret(),
        ];

        return new SimplifiedCompleteThreeDCommand(
            (string) $transaction->transactionId(),
            [
                'queryString' => $queryString
            ],
            $billerFields,
            env('BILLER_ROCKETGATE_TEST_MODE') ?? true
        );
    }

    /**
     * @param ChargeTransaction $transaction The transaction object
     * @return array
     */
    private function prepareMandatoryFieldsForExistingCreditCard(ChargeTransaction $transaction): array
    {
        $mandatoryFields = [
            // Credit Card information
            'cardHash'           => (string) $transaction->paymentInformation()->rocketGateCardHash(),
            // Biller mandatory fields
            'merchantCustomerId' => $transaction->billerChargeSettings()->merchantCustomerId(),
        ];

        if (!empty($transaction->billerChargeSettings()->referringMerchantId())) {
            $mandatoryFields['referringMerchantId'] = $transaction->billerChargeSettings()->referringMerchantId();
        }

        return $mandatoryFields;
    }

    /**
     * Prepare the mandatory fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareMandatoryFieldsForNewCreditCard(ChargeTransaction $transaction): array
    {
        return [
            // Credit Card information
            'number'          => $transaction->paymentInformation()->creditCardNumber()->cardNumber(),
            'expirationMonth' => $transaction->paymentInformation()->expirationMonth(),
            'expirationYear'  => $transaction->paymentInformation()->expirationYear(),
            'cvv'             => $transaction->paymentInformation()->cvv(),
        ];
    }

    /**
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareCommonMandatoryFields(ChargeTransaction $transaction): array
    {
        return [
            // Charge information
            'amount'           => $transaction->chargeInformation()->amount()->value(),
            'currency'         => $transaction->chargeInformation()->currency()->code(),
            // Biller mandatory fields
            'merchantId'       => $transaction->billerChargeSettings()->merchantId(),
            'merchantPassword' => $transaction->billerChargeSettings()->merchantPassword(),
        ];
    }

    /**
     * Prepare the rebill fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array|null
     */
    private function prepareOptionalRebillDetails(ChargeTransaction $transaction): ?array
    {
        $rebill = $transaction->chargeInformation()->rebill();

        if (!empty($rebill)) {
            return [
                'amount'    => $rebill->amount()->value(),
                'frequency' => $rebill->frequency(),
                'start'     => $rebill->start(),
            ];
        }

        return null;
    }

    /**
     * Prepare the customer fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareOptionalCustomerDetails(ChargeTransaction $transaction): array
    {
        /** @var CreditCardInformation $paymentInformation */
        $paymentInformation = $transaction->paymentInformation();

        $customerDetails = [];

        if ($paymentInformation->creditCardOwner() instanceof CreditCardOwner) {
            $customerDetails = [
                'firstName' => $paymentInformation->creditCardOwner()->ownerFirstName(),
                'lastName'  => $paymentInformation->creditCardOwner()->ownerLastName(),
                'email'     => (string) $paymentInformation->creditCardOwner()->ownerEmail()
            ];
        }
        if ($paymentInformation->creditCardBillingAddress() instanceof CreditCardBillingAddress) {
            $customerDetails = array_merge(
                $customerDetails,
                [
                    'address' => $paymentInformation->creditCardBillingAddress()->ownerAddress(),
                    'city'    => $paymentInformation->creditCardBillingAddress()->ownerCity(),
                    'state'   => $paymentInformation->creditCardBillingAddress()->ownerState(),
                    'zipCode' => $paymentInformation->creditCardBillingAddress()->ownerZip(),
                    'phone'   => $paymentInformation->creditCardBillingAddress()->ownerPhoneNo(),
                    'country' => $paymentInformation->creditCardBillingAddress()->ownerCountry()
                ]
            );
        }
        return $customerDetails;
    }

    /**
     * Get the mandatory fields for the rocketgate request
     * @param ChargeTransaction $transaction Transaction entity
     * @return array
     */
    private function prepareOptionalBillerFields(ChargeTransaction $transaction): array
    {
        /** @var RocketGateChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        return [
            'merchantSiteId'     => $billerChargeSettings->merchantSiteId(),
            'merchantAccount'    => $billerChargeSettings->merchantAccount(),
            'merchantProductId'  => $billerChargeSettings->merchantProductId(),
            'merchantDescriptor' => $billerChargeSettings->merchantDescriptor(),
            'merchantCustomerId' => $billerChargeSettings->merchantCustomerId(),
            'merchantInvoiceId'  => $billerChargeSettings->merchantInvoiceId(),
            'ipAddress'          => $billerChargeSettings->ipAddress(),
            'sharedSecret'       => $billerChargeSettings->sharedSecret(),
            'simplified3DS'      => $billerChargeSettings->simplified3DS(),
        ];
    }
}
