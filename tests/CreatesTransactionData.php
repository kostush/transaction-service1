<?php
declare(strict_types=1);

namespace Tests;

use DateTimeImmutable;
use Probiller\Common\BillerUnifiedGroupErrorResponse;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\CreateUpdateRebillTransactionTrait;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\PerformLegacyNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformQyssoNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill as RebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformEpochNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateUpdateRebillBillerFields;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateExistingCreditCardBillerFields;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation as NewCreditCardCommandInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation as ExistingCreditCardCommandInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member as CommandMember;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment as CommandPayment;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill as CommandRebill;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerInteractionId;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Currency;
use ProBillerNG\Transaction\Domain\Model\Email;
use ProBillerNG\Transaction\Domain\Model\EpochBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Model\InvoiceId;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateRebillUpdateSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingUnifiedGroupError;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateUnifiedGroupError;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateErrorCodes;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;

trait CreatesTransactionData
{
    use CreateUpdateRebillTransactionTrait;

    /**
     * @param array $data Override Data
     *
     * @return CreditCardInformation
     * @throws Exception
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidCreditCardExpirationDateException
     */
    protected function createCreditCardInformation(array $data = null): CreditCardInformation
    {
        return CreditCardInformation::create(
            $data['cvv2Check'] ?? true,
            $this->createCreditCardNumber($data),
            $this->createCreditCardOwner($data),
            $this->createCreditCardBillingAddress($data),
            $data['cvv'] ?? (string) $this->faker->numberBetween(100, 999),
            $data['expirationMonth'] ?? $this->faker->numberBetween(1, 12),
            $data['expirationYear'] ?? $this->faker->numberBetween(2025, 2030)
        );
    }

    /**
     * @return CheckInformation
     */
    protected function createCheckInformation(): CheckInformation
    {
        return new CheckInformation(
            'routingNumber',
            'accountNumber',
            false,
            'sslast4',
            null,
            null
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return CreditCardNumber
     * @throws Exception
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     */
    protected function createCreditCardNumber(array $data = null): CreditCardNumber
    {
        // Invalid card used just for tests. NOT A REAL CLIENT CARD
        return CreditCardNumber::create($data['number'] ?? $_ENV['ROCKETGATE_COMMON_CARD_NUMBER']);
    }

    /**
     * @param array|null $data Override Data
     * @return CreditCardOwner
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     */
    protected function createCreditCardOwner(array $data = null): CreditCardOwner
    {
        return CreditCardOwner::create(
            $data['ownerFirstName'] ?? $this->faker->name,
            $data['ownerLastName'] ?? $this->faker->lastName,
            $this->createEmail($data),
            $data['ownerUserName'] ?? null,
            $data['ownerPassword'] ?? null
        );
    }

    /**
     * @param array|null $data Override Data
     * @return Email
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     */
    protected function createEmail(array $data = null): Email
    {
        return Email::create($data['email'] ?? $this->faker->email);
    }

    /**
     * @param array|null $data Override Data
     * @return CreditCardBillingAddress
     */
    protected function createCreditCardBillingAddress(array $data = null): CreditCardBillingAddress
    {
        return CreditCardBillingAddress::create(
            $data['ownerAddress'] ?? $this->faker->address,
            $data['ownerCity'] ?? $this->faker->city,
            $data['ownerCountry'] ?? 'country',
            $data['ownerState'] ?? 'state',
            $data['ownerZip'] ?? $this->faker->postcode,
            $data['ownerPhoneNumber'] ?? $this->faker->phoneNumber
        );
    }

    /**
     * @param array|null $data Override Data
     * @return RocketGateChargeSettings
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createRocketgateChargeSettings(array $data = null): RocketGateChargeSettings
    {
        return RocketGateChargeSettings::create(
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? 'merchantCustomerId',
            $data['merchantInvoiceId'] ?? 'invoiceId',
            $data['merchantAccount'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantSiteId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantProductId'] ?? $this->faker->uuid,
            $data['merchantDescriptor'] ?? 'descriptor',
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['referringMerchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['sharedSecret'] ?? (string) $this->faker->word,
            $data['simplified3DS'] ?? true
        );
    }

    /**
     * @param array|null $data Override Data
     * @return RocketGateRebillUpdateSettings
     * @throws \Exception
     */
    protected function createRocketGateRebillUpdateSettings(array $data = null): RocketGateRebillUpdateSettings
    {
        return RocketGateRebillUpdateSettings::create(
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? (string) $this->faker->numberBetween(1, 1000),
            $data['merchantInvoiceId'] ?? (string) $this->faker->numberBetween(1, 1000),
            $data['merchantAccount'] ?? null,
        );
    }

    /**
     * @param array|null $data Override Data
     * @return Rebill
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createRebill(array $data = null): Rebill
    {
        return Rebill::create(
            $data['frequency'] ?? $this->faker->numberBetween(1, 100),
            $data['start'] ?? $this->faker->numberBetween(1, 100),
            $this->createRebillAmount($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return Amount
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createAmount(array $data = null): Amount
    {
        return Amount::create($data['amount'] ?? $this->faker->randomFloat(2, 1, 100));
    }

    /**
     * @param array|null $data Override Data
     * @return Amount
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createRebillAmount(array $data = null): Amount
    {
        return Amount::create($data['rebillAmount'] ?? $this->faker->randomFloat(2, 1, 100));
    }

    /**
     * @param array|null $data Override Data
     * @return Currency
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createCurrency(array $data = null): Currency
    {
        return Currency::create($data['currency'] ?? 'USD');
    }

    /**
     * @param array|null $data Override Data
     * @return ChargeInformation
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createChargeInformationSingleCharge(array $data = null): ChargeInformation
    {
        return ChargeInformation::createSingleCharge(
            $this->createCurrency($data),
            $this->createAmount($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return ChargeInformation
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createChargeInformationWithRebill(array $data = null): ChargeInformation
    {
        return ChargeInformation::createWithRebill(
            $this->createCurrency($data),
            $this->createAmount($data),
            $this->createRebill($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return BillerInteraction
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    protected function createBillerInteraction(array $data = null): BillerInteraction
    {
        return BillerInteraction::create(
            $data['type'] ?? BillerInteraction::TYPE_REQUEST,
            $data['payload'] ?? json_encode(
                [
                    'guidNo'          => '123',
                    'reasonCode'      => RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                    'cardHash'        => 'card hash',
                    'cardDescription' => 'card description',
                    'approvedAmount'  => '2.25'
                ],
                JSON_THROW_ON_ERROR
            ),
            $data['createdAt'] ?? new DateTimeImmutable(),
            BillerInteractionId::create()
        );
    }

    /**
     * @param array|null $data Override Data
     * @return BillerInteraction
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    protected function createQyssoRequestBillerInteraction(array $data = null): BillerInteraction
    {
        return BillerInteraction::create(
            $data['type'] ?? BillerInteraction::TYPE_REQUEST,
            $data['payload'] ?? json_encode([], JSON_THROW_ON_ERROR),
            $data['createdAt'] ?? new DateTimeImmutable(),
            BillerInteractionId::create()
        );
    }

    /**
     * @param array|null $data Override Data
     * @return BillerInteraction
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    protected function createQyssoResponseBillerInteraction(array $data = null): BillerInteraction
    {
        return BillerInteraction::create(
            $data['type'] ?? BillerInteraction::TYPE_RESPONSE,
            $data['payload'] ?? json_encode(["TransID" => "1"], JSON_THROW_ON_ERROR),
            $data['createdAt'] ?? new DateTimeImmutable(),
            BillerInteractionId::create()
        );
    }

    /**
     * @param array|null $data Override Data
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     */
    protected function createPendingTransactionWithRebillForNewCreditCard(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill($data);

        return ChargeTransaction::createWithRebillOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill(),
            $command->useThreeD()
        );
    }

    /**
     * @param array|null $data Data
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createChargeTransactionWithoutRebillOnPumapay(array $data = null): ChargeTransaction
    {
        $command = new RetrievePumapayQrCodeCommand(
            $data['siteId'] ?? $this->faker->uuid,
            'EUR',
            1.00,
            null,
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );

        return ChargeTransaction::createSingleChargeOnPumapay(
            $command->siteId(),
            $command->amount(),
            PumaPayBillerSettings::PUMAPAY,
            $command->currency(),
            $command->businessId(),
            $command->businessModel(),
            $command->apiKey(),
            $command->title(),
            $command->description(),
            $command->rebill()
        );
    }

    /**
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createChargeTransactionWithRebillOnPumapay(): ChargeTransaction
    {
        $command = new RetrievePumapayQrCodeCommand(
            $this->faker->uuid,
            'EUR',
            1.00,
            new CommandRebill(1, 30, 10),
            'businessId',
            'businessModel',
            'apiKey',
            'Brazzers Membership',
            '1$ day then daily rebill at 1$ for 3 days'
        );

        return ChargeTransaction::createSingleChargeOnPumapay(
            $command->siteId(),
            $command->amount(),
            PumaPayBillerSettings::PUMAPAY,
            $command->currency(),
            $command->businessId(),
            $command->businessModel(),
            $command->apiKey(),
            $command->title(),
            $command->description(),
            $command->rebill()
        );
    }

    /**
     * @return RebillUpdateTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createCancelRebillRocketgateTransaction(): RebillUpdateTransaction
    {
        $previousTransaction = $this->createPendingRocketgateTransactionSingleCharge();

        return RebillUpdateTransaction::createCancelRebillTransaction(
            $previousTransaction,
            RocketGateBillerSettings::ROCKETGATE,
            $this->createCommandUpdateRebillBillerFields()
        );
    }

    /**
     * @param array $data Override Data
     * @return RebillUpdateTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createUpdateRebillTransaction(array $data = []): RebillUpdateTransaction
    {
        $command             = $this->createPerformRocketgateUpdateRebillCommand($data);
        $previousTransaction = $this->createPendingRocketgateTransactionSingleCharge();
        return $this->createRocketgateUpdateRebillTransaction($command, $previousTransaction);
    }

    /**
     * @param array|null $data Override Data
     * @return ChargeTransaction
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
    protected function createPendingTransactionWithRebillForExistingCreditCard(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformExistingCreditCardRocketgateSaleCommandWithRebill($data);

        return ChargeTransaction::createWithRebillOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill(),
            isset($data) && array_key_exists('requiredToUse3D', $data) ? (bool) $data['requiredToUse3D'] : false,
        );
    }

    /**
     * @param array|null $data              Override Data
     * @param array|null $billerInteraction Biller interaction
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidMerchantInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createPendingRocketgateTransactionSingleCharge(
        array $data = null,
        array $billerInteraction = null
    ): ChargeTransaction {
        $command = $this->createPerformRocketgateSaleCommandSingleCharge($data);

        $transaction = ChargeTransaction::createSingleChargeOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->useThreeD()
        );

        $transaction->addBillerInteraction($this->createBillerInteraction($billerInteraction));
        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                [
                    'type' => BillerInteraction::TYPE_RESPONSE,
                ]
            )
        );

        if (isset($data['threedsVersion'])) {
            $transaction->updateThreedsVersion($data['threedsVersion']);
        }

        return $transaction;
    }

    /**
     * @param array|null $data       Data to overwrite default values
     * @param bool       $withRebill If the command also contain rebill data
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createPendingQyssoTransaction(array $data = null, bool $withRebill = false): ChargeTransaction
    {
        $command = $this->createPerformQyssoNewSaleCommand($data, $withRebill);

        if ($command->rebill() === null) {
            $transaction = ChargeTransaction::createSingleChargeOnEpoch(
                $command->siteId(),
                $command->siteName(),
                QyssoBillerSettings::QYSSO,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        } else {
            $transaction = ChargeTransaction::createWithRebillOnEpoch(
                $command->siteId(),
                $command->siteName(),
                QyssoBillerSettings::QYSSO,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->rebill(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        }

        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                ['type' => BillerInteraction::TYPE_RESPONSE,]
            )
        );

        return $transaction;
    }

    /**
     * @param array|null $data       Data to overwrite default values
     * @param bool       $withRebill If the command also contain rebill data
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createPendingEpochTransaction(array $data = null, bool $withRebill = false): ChargeTransaction
    {
        $command = $this->createPerformEpochNewSaleCommand($data, $withRebill);

        if ($command->rebill() === null) {
            $transaction = ChargeTransaction::createSingleChargeOnEpoch(
                $command->siteId(),
                $command->siteName(),
                EpochBillerSettings::EPOCH,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        } else {
            $transaction = ChargeTransaction::createWithRebillOnEpoch(
                $command->siteId(),
                $command->siteName(),
                EpochBillerSettings::EPOCH,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->rebill(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        }

        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                ['type' => BillerInteraction::TYPE_RESPONSE,]
            )
        );

        return $transaction;
    }

    /**
     * @param array|null $data data
     * @return ChargeTransaction
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     */
    protected function createPendingLegacyTransaction(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformLegacyNewSaleCommand($data);

        $charge = (ChargesCollection::createFromArray($command->charges()))->getMainPurchase();

        $transaction = ChargeTransaction::createTransactionOnLegacy(
            $charge->siteId(),
            LegacyBillerChargeSettings::LEGACY,
            ChargeInformation::createChargeInformationFromCharge($charge),
            $command->paymentType(),
            LegacyBillerChargeSettings::create(
                $command->legacyMemberId(),
                $command->billerName(),
                $command->returnUrl(),
                $command->postbackUrl(),
                $command->others()
            ),
            $command->paymentMethod(),
            (!empty($command->member()) ? $command->member()->userName() : null),
            (!empty($command->member()) ? $command->member()->password() : null)
        );

        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                ['type' => BillerInteraction::TYPE_RESPONSE,]
            )
        );

        return $transaction;
    }

    /**
     * @param array|null $data data
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function createPendingLegacyTransactionWithNoMember(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformLegacyNewSaleCommand($data);

        $charge = (ChargesCollection::createFromArray($command->charges()))->getMainPurchase();

        $transaction = ChargeTransaction::createTransactionOnLegacy(
            $charge->siteId(),
            LegacyBillerChargeSettings::LEGACY,
            ChargeInformation::createChargeInformationFromCharge($charge),
            $command->paymentType(),
            LegacyBillerChargeSettings::create(
                $command->legacyMemberId(),
                $command->billerName(),
                $command->returnUrl(),
                $command->postbackUrl(),
                $command->others()
            ),
            $command->paymentMethod(),
            null,
            null
        );

        $transaction->addBillerInteraction($this->createBillerInteraction());
        $transaction->addBillerInteraction(
            $this->createBillerInteraction(
                ['type' => BillerInteraction::TYPE_RESPONSE,]
            )
        );

        return $transaction;
    }

    /**
     * @param array|null $data Override Data
     * @return PerformRocketgateNewCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createPerformRocketgateSaleCommandSingleCharge(
        array $data = null
    ): PerformRocketgateNewCreditCardSaleCommand {
        return new PerformRocketgateNewCreditCardSaleCommand(
            $data['siteId'] ?? $this->faker->uuid,
            $data['amount'] ?? $this->faker->randomFloat(2, 1, 100),
            $data['currency'] ?? 'USD',
            $this->createNewCreditCardCommandPayment($data),
            $this->createCommandBillerFields($data),
            null,
            $data['useThreeD'] ?? false,
        );
    }

    /**
     * @param array|null $data       Data to overwrite default values
     * @param bool       $withRebill If the command also contain rebill data
     * @return PerformQyssoNewSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createPerformQyssoNewSaleCommand(
        array $data = null,
        bool $withRebill = false
    ): PerformQyssoNewSaleCommand {
        $rebill = new RebillCommand(
            20.55,
            30,
            15
        );

        $billerSettings = QyssoBillerSettings::create(
            $data['companyNum'] ?? $_ENV['QYSSO_COMPANY_NUM_2'],
            $data['personalHashKey'] ?? $_ENV['QYSSO_PERSONAL_HASH_KEY_2'],
            $data['redirect_url'] ?? 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/{jwt}',
            $data['notification_url'] ?? 'https://postback-gateway.probiller.com/api/postback/{receiverId}/session/{sessionId}'
        );

        $member = new Member(
            $data['firstName'] ?? 'firstName',
            $data['lastName'] ?? 'lastName',
            $data['username'] ?? 'username',
            $data['email'] ?? 'email',
            null,
            null,
            $data['zipCode'] ?? 'zipCode',
            null,
            null,
            null,
            $data['password'] ?? 'password'
        );

        return new PerformQyssoNewSaleCommand(
            $data['sessionId'] ?? $this->faker->uuid,
            $data['siteId'] ?? $this->faker->uuid,
            $data['siteName'] ?? 'www.realitykings.com',
            $data['clientIp'] ?? $this->faker->ipv4,
            $data['amount'] ?? 9.99,
            $data['currency'] ?? 'USD',
            [
                'type'        => $data['paymentType'] ?? 'banktransfer',
                'method'      => $data['paymentMethod'] ?? 'zelle',
                'information' => [
                    'member' => [
                        'userName'  => $data['username'] ?? 'username',
                        'password'  => $data['password'] ?? 'password',
                        'firstName' => $data['firstName'] ?? 'firstName',
                        'lastName'  => $data['lastName'] ?? 'lastName',
                        'email'     => $data['email'] ?? 'email',
                        'zipCode'   => $data['zipCode'] ?? 'zipCode',
                    ]
                ]
            ],
            $data['tax'] ?? [],
            $data['billerFields'] ?? $billerSettings,
            $member,
            $withRebill ? $rebill : null,
        );
    }

    /**
     * @param array|null $data       Data to overwrite default values
     * @param bool       $withRebill If the command also contain rebill data
     * @return PerformEpochNewSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createPerformEpochNewSaleCommand(
        array $data = null,
        bool $withRebill = false
    ): PerformEpochNewSaleCommand {
        $rebill = new RebillCommand(
            20.55,
            30,
            15
        );

        $billerSettings = EpochBillerChargeSettings::create(
            $data['clientId'] ?? $_ENV['EPOCH_CLIENT_ID'],
            $data['clientKey'] ?? $_ENV['EPOCH_CLIENT_KEY'],
            $data['clientVerificationKey'] ?? $_ENV['EPOCH_CLIENT_VERIFICATION_KEY'],
            $data['redirect_url'] ?? 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/{jwt}',
            $data['notification_url'] ?? 'https://postback-gateway.probiller.com/api/postbacks/{UUID}',
            InvoiceId::create()
        );

        $member = new Member(
            $data['firstName'] ?? 'firstName',
            $data['lastName'] ?? 'lastName',
            $data['username'] ?? 'username',
            $data['email'] ?? 'email',
            null,
            null,
            $data['zipCode'] ?? 'zipCode',
            null,
            null,
            null,
            $data['password'] ?? 'password'
        );

        return new PerformEpochNewSaleCommand(
            $data['sessionId'] ?? $this->faker->uuid,
            $data['siteId'] ?? $this->faker->uuid,
            $data['siteName'] ?? 'www.realitykings.com',
            $data['amount'] ?? 9.99,
            $data['currency'] ?? 'EUR',
            [
                'payment' => [
                    'type'        => $data['paymentType'] ?? 'cc',
                    'method'      => $data['paymentMethod'] ?? 'visa',
                    'information' => [
                        'member' => [
                            'userName'  => $data['username'] ?? 'username',
                            'password'  => $data['password'] ?? 'password',
                            'firstName' => $data['firstName'] ?? 'firstName',
                            'lastName'  => $data['lastName'] ?? 'lastName',
                            'email'     => $data['email'] ?? 'email',
                            'zipCode'   => $data['zipCode'] ?? 'zipCode',
                        ]
                    ]
                ]
            ],
            $data['crossSales'] ?? [],
            $data['tax'] ?? [],
            $data['billerFields'] ?? $billerSettings,
            $member,
            $withRebill ? $rebill : null,
        );
    }

    /**
     * @param array|null $data Data to overwrite default values
     * @return PerformLegacyNewSaleCommand
     */
    protected function createPerformLegacyNewSaleCommand(
        array $data = null
    ): PerformLegacyNewSaleCommand {

        $member = new Member(
            $data['firstName'] ?? 'firstName',
            $data['lastName'] ?? 'lastName',
            $data['username'] ?? 'username',
            $data['email'] ?? 'email',
            null,
            null,
            $data['zipCode'] ?? 'zipCode',
            null,
            null,
            null,
            $data['password'] ?? 'password'
        );

        $charges = [
            [
                'siteId'         => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
                "amount"         => 14.97,
                "currency"       => "USD",
                "productId"      => 15,
                "isMainPurchase" => true,
                'rebill'         => [
                    'amount'    => 10,
                    'frequency' => 365,
                    'start'     => 30
                ],
                'tax'            => [
                    'initialAmount'    => [
                        'beforeTaxes' => 14.23,
                        'taxes'       => 0.74,
                        'afterTaxes'  => 14.97
                    ],
                    'rebillAmount'     => [
                        'beforeTaxes' => 9.5,
                        'taxes'       => 0.5,
                        'afterTaxes'  => 10
                    ],
                    'taxApplicationId' => '60bf5bcb-ac64-496c-acc5-9c7cf54a1869',
                    'taxName'          => 'Tax Name',
                    'taxRate'          => 0.05,
                    'taxType'          => 'vat'
                ]
            ]
        ];

        return new PerformLegacyNewSaleCommand(
            $data['paymentType'] ?? 'cc',
            $data['charges'] ?? $charges,
            $data['returnUrl'] ?? 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/{jwt}',
            $data['postbackUrl'] ?? 'https://postback-gateway.probiller.com/api/postbacks/{UUID}',
            $data['billerName'] ?? 'vendo',
            $data['paymentMethod'] ?? 'visa',
            $member,
            $data['legacyMemberId'] ?? 10,
            $data['others'] ?? [],
        );
    }

    /**
     * @param array|null $data Override Data
     * @return PerformRocketgateExistingCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createPerformExistingCreditCardRocketgateSaleCommandSingleCharge(
        array $data = null
    ): PerformRocketgateExistingCreditCardSaleCommand {
        return new PerformRocketgateExistingCreditCardSaleCommand(
            $data['siteId'] ?? $this->faker->uuid,
            $data['amount'] ?? $this->faker->randomFloat(2, 1, 100),
            $data['currency'] ?? 'USD',
            $this->createExistingCreditCardCommandPayment($data),
            $this->createExistingCreditCardBillerFields($data),
            null
        );
    }

    /**
     * @param array|null $data Override Data
     * @return PerformRocketgateNewCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    protected function createPerformRocketgateNewCreditCardSaleCommandWithRebill(
        array $data = null
    ): PerformRocketgateNewCreditCardSaleCommand {
        return new PerformRocketgateNewCreditCardSaleCommand(
            isset($data) && array_key_exists('siteId', $data) ? $data['siteId'] : $this->faker->uuid,
            isset($data) && array_key_exists('amount', $data) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            isset($data) && array_key_exists('currency', $data) ? $data['currency'] : 'USD',
            $this->createNewCreditCardCommandPayment($data),
            $this->createCommandBillerFields($data),
            $this->createCommandRebill($data),
            isset($data) && array_key_exists('useThreeD', $data) ? $data['useThreeD'] : false,
            isset($data) && array_key_exists('returnUrl', $data) ? $data['returnUrl'] : null,
        );
    }

    /**
     * @param array|null $data Data
     * @return LookupThreeDsTwoCommand
     * @throws Exception
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    protected function createLookupThreeDsTwoCommand(
        array $data = null
    ): LookupThreeDsTwoCommand {
        return new LookupThreeDsTwoCommand(
            isset($data)
            && array_key_exists('deviceFingerprintingId', $data) ? $data['deviceFingerprintingId'] : $this->faker->uuid,
            isset($data)
            && array_key_exists('previousTransactionId', $data) ? $data['previousTransactionId'] : $this->faker->uuid,
            isset($data) && array_key_exists('redirectUrl', $data) ? $data['redirectUrl'] : $this->faker->url,
            $this->createNewCreditCardCommandPayment($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return PerformRocketgateExistingCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createPerformExistingCreditCardRocketgateSaleCommandWithRebill(
        array $data = null
    ): PerformRocketgateExistingCreditCardSaleCommand {
        return new PerformRocketgateExistingCreditCardSaleCommand(
            isset($data) && array_key_exists('siteId', $data) ? $data['siteId'] : $this->faker->uuid,
            isset($data) && array_key_exists('amount', $data) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            isset($data) && array_key_exists('currency', $data) ? $data['currency'] : 'USD',
            $this->createExistingCreditCardCommandPayment($data),
            $this->createExistingCreditCardBillerFields($data),
            $this->createCommandRebill($data)
        );
    }

    /**
     * @param array $data Override Data
     * @return PerformRocketgateCancelRebillCommand
     */
    protected function createPerformRocketGateCancelRebillCommand(
        array $data = []
    ): PerformRocketgateCancelRebillCommand {

        $merchantSiteId = $this->faker->numberBetween(1, 100);

        return new PerformRocketgateCancelRebillCommand(
            $data['transactionId'] ?? $this->faker->uuid,
            $data['merchantId'] ?? $_ENV['ROCKETGATE_MERCHANT_ID_2'],
            $data['merchantPassword'] ?? $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
            $data['merchantCustomerId'] ?? uniqid((string) $merchantSiteId, true),
            $data['merchantInvoiceId'] ?? uniqid((string) $merchantSiteId, true)
        );
    }

    /**
     * @param array $data Override Data
     * @return PerformRocketgateUpdateRebillCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createPerformRocketgateUpdateRebillCommand(
        array $data = []
    ): PerformRocketgateUpdateRebillCommand {
        return new PerformRocketgateUpdateRebillCommand(
            $data['transactionId'] ?? $this->faker->uuid,
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantInvoiceId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantAccount'] ?? null,
            $data['rebill'] ?? [
                'amount'    => 20,
                'start'     => 365,
                'frequency' => 365
            ],
            isset($data['amount']) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            isset($data['currency']) ? $data['currency'] : 'USD',
            $data['payment'] ?? [
                'method'      => 'cc',
                'information' => [
                    'cardHash' => $_ENV['ROCKETGATE_CARD_HASH_1']
                ]
            ]
        );
    }

    /**
     * @param array $data overrides
     * @return PerformNetbillingUpdateRebillCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    protected function createPerformNetbillingUpdateRebillCommand(
        array $data = []
    ): PerformNetbillingUpdateRebillCommand {
        return new PerformNetbillingUpdateRebillCommand(
            $data['transactionId'] ?? $this->faker->uuid,
            $data['siteTag'] ?? $_ENV['NETBILLING_SITE_TAG'],
            $data['accountId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['rebill'] ?? [
                'amount'    => 20,
                'start'     => 365,
                'frequency' => 365
            ],
            isset($data['amount']) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            $data['payment'] ?? [
                'type'        => 'cc',
                'information' => [
                    'cardHash' => $_ENV['ROCKETGATE_CARD_HASH_1']
                ]
            ],
            $data['binRouting'] ?? '',
            isset($data['currency']) ? $data['currency'] : 'USD'
        );
    }

    /**
     * @param array|null $data Override Data
     * @return CommandRebill
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createCommandRebill(array $data = null): CommandRebill
    {
        return new CommandRebill(
            $data['rebillAmount'] ?? $this->faker->randomFloat(2, 1, 100),
            $data['frequency'] ?? $this->faker->numberBetween(1, 100),
            $data['start'] ?? $this->faker->numberBetween(1, 100)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return CommandPayment
     * @throws Exception
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    protected function createNewCreditCardCommandPayment(array $data = null): CommandPayment
    {
        return new CommandPayment(
            $data['type'] ?? 'cc',
            $this->createNewCreditCardCommandInformation($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return CommandPayment
     * @throws Exception
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createExistingCreditCardCommandPayment(array $data = null): CommandPayment
    {
        return new CommandPayment(
            $data['type'] ?? 'cc',
            $this->createExistingCreditCardCommandInformation($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return NewCreditCardCommandInformation
     * @throws Exception
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingCreditCardInformationException
     */
    protected function createNewCreditCardCommandInformation(array $data = null): NewCreditCardCommandInformation
    {
        return new NewCreditCardCommandInformation(
            $data['number'] ?? $this->faker->creditCardNumber('Visa'),
            $data['expirationMonth'] ?? (string) $this->faker->numberBetween(1, 12),
            $data['expirationYear'] ?? $this->faker->numberBetween(2025, 2030),
            $data['cvv'] ?? (string) $this->faker->numberBetween(100, 999),
            $data['member'] ?? $this->createCommandMember($data)
        );
    }

    /**
     * @param array|null $data Override Data
     * @return ExistingCreditCardCommandInformation
     * @throws Exception
     * @throws MissingCreditCardInformationException
     */
    protected function createExistingCreditCardCommandInformation(
        array $data = null
    ): ExistingCreditCardCommandInformation {
        return new ExistingCreditCardCommandInformation(
            $data['cardHash'] ?? $_ENV['ROCKETGATE_CARD_HASH_1']
        );
    }

    /**
     * @param array|null $data Override Data
     * @return CommandMember
     */
    protected function createCommandMember(array $data = null): CommandMember
    {
        return new CommandMember(
            $data['ownerFirstName'] ?? $this->faker->name,
            $data['ownerLastName'] ?? $this->faker->lastName,
            $data['userName'] ?? $this->faker->userName,
            $data['email'] ?? $this->faker->email,
            $data['ownerPhoneNumber'] ?? $this->faker->phoneNumber,
            $data['ownerAddress'] ?? $this->faker->address,
            $data['ownerZip'] ?? $this->faker->postcode,
            $data['ownerCity'] ?? $this->faker->city,
            $data['ownerState'] ?? 'state',
            $data['ownerCountry'] ?? 'country'
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return RocketGateChargeSettings
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createCommandBillerFields(array $data = null): RocketGateChargeSettings
    {
        $merchantAccount = (!empty($data) && array_key_exists('merchantAccount', $data))
            ? $data['merchantAccount'] : (string) $this->faker->numberBetween(1, 100);

        return RocketGateChargeSettings::create(
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? 'merchantCustomerId',
            $data['merchantInvoiceId'] ?? 'invoiceId',
            $merchantAccount,
            $data['merchantSiteId'] ?? $this->faker->uuid,
            $data['merchantProductId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantDescriptor'] ?? 'descriptor',
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['referringMerchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['sharedSecret'] ?? (string) $this->faker->word,
            $data['simplified3DS'] ?? true,
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return RocketGateUpdateRebillBillerFields
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createCommandUpdateRebillBillerFields(array $data = null): RocketGateUpdateRebillBillerFields
    {
        return new RocketGateUpdateRebillBillerFields(
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantInvoiceId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantAccount'] ?? null
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return RocketGateExistingCreditCardBillerFields
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createExistingCreditCardBillerFields(
        array $data = null
    ): RocketGateExistingCreditCardBillerFields {
        return new RocketGateExistingCreditCardBillerFields(
            $data['merchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['merchantCustomerId'] ?? 'merchantCustomerId',
            $data['merchantInvoiceId'] ?? 'invoiceId',
            $data['merchantAccount'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantSiteId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantProductId'] ?? $this->faker->uuid,
            $data['merchantDescriptor'] ?? 'descriptor',
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['referringMerchantId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['sharedSecret'] ?? $this->faker->word,
            $data['simplified3DS'] ?? true
        );
    }

    /**
     * @param array $attributePathsToExclude Attribute Path To Exclude.
     *
     * @return array
     */
    protected function createRocketgateBillerResponse(array $attributePathsToExclude = []): array
    {
        $data = [
            'code'     => 0,
            'reason'   => 'Just for test',
            'request'  => [
                'version'            => 'P6.3',
                'cvv2Check'          => 'TRUE',
                'amount'             => '0.12',
                'currency'           => 'USD',
                'cardNo'             => '*******',
                'expireMonth'        => 12,
                'expireYear'         => 2020,
                'cvv2'               => '*******',
                'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_3'],
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_3'],
                'rebillAmount'       => '0.12',
                'rebillFrequency'    => 30,
                'rebillStart'        => 5,
                'customerFirstName'  => 'Gisele',
                'customerLastName'   => 'Framboise',
                'email'              => 'gisele.framboise@fruity.com',
                'billingCountry'     => 'CA',
                'billingAddress'     => '7777 Decarie',
                'billingCity'        => 'Montreal',
                'billingState'       => 'QC',
                'billingZipCode'     => 'H1H1H1',
                'customerPhoneNo'    => '514 222-5555',
                'merchantSiteID'     => '7',
                'merchantAccount'    => '5',
                'merchantProductID'  => '42456483-a6ae-4ed7-b43f-e19635697f28',
                'merchantCustomerID' => '0de9e9f5-5d4dba216b15c5.38711686',
                'merchantInvoiceID'  => '4165c1cddd83a9cb8.99590556',
                'ipAddress'          => '205.45.120.42',
                'transactionType'    => 'CC_CONFIRM',
                'referenceGUID'      => '100016C779F1433'
            ],
            'response' => [
                'authNo'             => '859235',
                'merchantInvoiceID'  => '4165c1cddd83a9cb8.99590556',
                'merchantAccount'    => '5',
                'approvedAmount'     => '0.12',
                'cardIssuerPhone'    => '800-359-8092',
                'cardLastFour'       => '2751',
                'cardIssuerURL'      => 'HTTP:\/\/WWW.FIRSTINTLBANK.COM\/',
                'version'            => '1.0',
                'merchantCustomerID' => '0de9e9f5-5d4dba216b15c5.38711686',
                'cvv2Code'           => 'M',
                'reasonCode'         => '0',
                'retrievalNo'        => '100016c779f1433',
                'cardIssuerName'     => 'FIRST INTERNATIONAL BANK AND TRUST',
                'payType'            => 'CREDIT',
                'cardHash'           => $_ENV['ROCKETGATE_CARD_HASH_1'],
                'cardDebitCredit'    => '0',
                'cardRegion'         => '1',
                'cardDescription'    => 'PREPAID',
                'cardCountry'        => 'US',
                'cardType'           => 'VISA',
                'bankResponseCode'   => '0',
                'approvedCurrency'   => 'USD',
                'guidNo'             => '100016C779F1433',
                'cardExpiration'     => '1220',
                'balanceAmount'      => '2.36',
                'balanceCurrency'    => 'CAD',
                'responseCode'       => '0'
            ]
        ];

        foreach ($attributePathsToExclude as $value) {
            $excludes = explode('.', $value);

            if (count($excludes) === 1) {
                unset($data[$excludes[0]]);
            }
            if (count($excludes) === 2) {
                if ($excludes[1] === '*') {
                    $data[$excludes[0]] = null;
                } else {
                    unset($data[$excludes[0]][$excludes[1]]);
                }
            }
        }

        return $data;
    }

    /**
     * @return BillerInteractionCollection
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    protected function billerInteractionCollection(): BillerInteractionCollection
    {
        $collection = new BillerInteractionCollection();

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'      => BillerInteraction::TYPE_REQUEST,
                    'payload'   => json_encode(
                        [
                            "version"            => "P6.3",
                            "cvv2Check"          => "TRUE",
                            "billingType"        => "I",
                            "amount"             => "123",
                            "currency"           => "USD",
                            "cardNo"             => "*******",
                            "expireMonth"        => 12,
                            "expireYear"         => 2021,
                            "cvv2"               => "*******",
                            'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_3'],
                            'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_3'],
                            "use3DSecure"        => "TRUE",
                            "rebillAmount"       => "29.99",
                            "rebillFrequency"    => 30,
                            "rebillStart"        => 5,
                            "customerFirstName"  => "Gisele",
                            "customerLastName"   => "Framboise",
                            "email"              => "gisele.framboise@test.mindgeek.com",
                            "billingCountry"     => "CA",
                            "billingAddress"     => "7777 Decarie",
                            "billingCity"        => "Montreal",
                            "billingZipCode"     => "H1H1H1",
                            "customerPhoneNo"    => "514 222-5555",
                            "merchantProductID"  => "42456483-a6ae-4ed7-b43f-e19635697f28",
                            "merchantCustomerID" => "4165c1cddd82cce24.92280667",
                            "merchantInvoiceID"  => "4165c1cddd83a9cb8.99590556",
                            "ipAddress"          => "205.45.120.42",
                            "transactionType"    => "CC_PURCHASE"
                        ]
                    ),
                    'createdAt' => new DateTimeImmutable()
                ]
            )
        );

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'      => BillerInteraction::TYPE_RESPONSE,
                    'payload'   => json_encode(
                        [
                            "merchantInvoiceID"  => "4165c1cddd83a9cb8.99590556",
                            "merchantAccount"    => "2",
                            "approvedAmount"     => "123.0",
                            "cardLastFour"       => "6130",
                            "PAREQ"              => "SimulatedPAREQ1000170E86FCD6B",
                            "version"            => "1.0",
                            "merchantCustomerID" => "4165c1cddd82cce24.92280817",
                            "acsURL"             => "https=>\/\/dev1.rocketgate.com\/hostedpage\/3DSimulator.jsp",
                            "reasonCode"         => RocketgateErrorCodes::RG_CODE_3DS_AUTH_REQUIRED,
                            "payType"            => "CREDIT",
                            "cardHash"           => $this->cardHash(),
                            "cardDebitCredit"    => "0",
                            "cardDescription"    => $this->cardDescription(),
                            "cardType"           => "VISA",
                            "approvedCurrency"   => "USD",
                            "guidNo"             => "1000170E86FCD6B",
                            "cardExpiration"     => "1220",
                            "responseCode"       => "2"
                        ]
                    ),
                    'createdAt' => new DateTimeImmutable()
                ]
            )
        );


        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'      => BillerInteraction::TYPE_REQUEST,
                    'payload'   => json_encode(
                        [
                            "version"            => "P6.3",
                            "billingType"        => "I",
                            'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_3'],
                            'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_3'],
                            "merchantCustomerID" => "4165c1cddd82cce24.92280667",
                            "merchantInvoiceID"  => "4165c1cddd83a9cb8.99590556",
                            "PARES"              => "SimulatedPARES10001709ADEECB3",
                            "amount"             => "123",
                            "currency"           => "USD",
                            "cvv2Check"          => "FALSE",
                            "rebillAmount"       => "29.99",
                            "rebillFrequency"    => 30,
                            "rebillStart"        => 5,
                            "merchantProductID"  => "42456483-a6ae-4ed7-b43f-e19635697f28",
                            "ipAddress"          => "205.45.120.42",
                            "transactionType"    => "CC_CONFIRM",
                            "referenceGUID"      => "10001709ADF3705"
                        ]
                    ),
                    'createdAt' => new DateTimeImmutable()
                ]
            )
        );

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'      => BillerInteraction::TYPE_RESPONSE,
                    'payload'   => json_encode(
                        [
                            "authNo"             => "811516",
                            "merchantInvoiceID"  => "4165c1cddd83a9cb8.99590556",
                            "merchantAccount"    => "2",
                            "approvedAmount"     => "123.0",
                            "cardLastFour"       => "6130",
                            "version"            => "1.0",
                            "merchantCustomerID" => "4165c1cddd82cce24.92280817",
                            "ECI"                => "05",
                            "reasonCode"         => "0",
                            "payType"            => "CREDIT",
                            "INTERNAL0902091104" => "1000170E8706757",
                            "cardHash"           => $this->cardHash(),
                            "cardDebitCredit"    => "0",
                            "cardDescription"    => $this->cardDescription(),
                            "cardType"           => "VISA",
                            "bankResponseCode"   => "0",
                            "approvedCurrency"   => "USD",
                            "guidNo"             => "1000170E8706757",
                            "cardExpiration"     => "1220",
                            "responseCode"       => "0"
                        ]
                    ),
                    'createdAt' => new DateTimeImmutable()
                ]
            )
        );

        return $collection;
    }

    /**
     * @param array $data Data
     * @return BillerInteractionCollection
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    protected function epochBillerInteractionCollection(array $data = []): BillerInteractionCollection
    {
        $collection = new BillerInteractionCollection();

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'    => BillerInteraction::TYPE_REQUEST,
                    'payload' => json_encode(
                        [
                            'client_id'         => $_ENV['EPOCH_CLIENT_ID'],
                            'invoice_id'        => '7b5bae3c-9545-4ff2-ae6e-0da2a1b6a2ec',
                            'redirect_url'      => 'http:\/\/purchase-gateway.probiller.com\/api\/v1\/purchase\/thirdParty\/return\/[jwt]',
                            'purchases'         => [
                                [
                                    'passthru'     => [
                                        'ngSessionId'     => '91acb136-ce4a-42c7-a4bb-eb910e95d3ba',
                                        'ngTransactionId' => '3dc1e88b-ab09-4ba9-9e9c-28343a0030ff'
                                    ],
                                    'postback_url' => 'https:\/\/postback-gateway.probiller.com\/api\/postbacks\/[UUID]',
                                    'site'         => 'www.realitykings.com',
                                    'billing'      => [
                                        'currency'  => 'USD',
                                        'initial'   => [
                                            'amount'             => '14.97',
                                            'valid_until_period' => 30,
                                            'valid_until_unit'   => 'DAY'
                                        ],
                                        'recurring' => [
                                            [
                                                'amount'    => '10',
                                                'frequency' => 365,
                                                'unit'      => 'DAY'
                                            ]
                                        ]
                                    ],
                                    'username'     => 'username',
                                    'password'     => 'password',
                                    'descriptions' => [
                                        'Initial Purchase: 14.23, Tax Name: 5%, Tax Amount: 0.74, Total Charge: 14.97',
                                        'Recurring Purchase: 9.5, Tax Name: 5%, Tax Amount: 0.5, Total Charge: 10'
                                    ]
                                ]
                            ],
                            'customer'          => [
                                'name'        => 'firstName lastName',
                                'email'       => 'email@test.mindgeek.com',
                                'postal_code' => 'zipCode'
                            ],
                            'additional_offers' => [
                                [
                                    'passthru'     => [
                                        'ngSessionId'     => '91acb136-ce4a-42c7-a4bb-eb910e95d3ba',
                                        'ngTransactionId' => '90607ec0-e7f2-42d8-8d6a-cf4ac6436256'
                                    ],
                                    'postback_url' => 'https:\/\/postback-gateway.probiller.com\/api\/postbacks\/[UUID]',
                                    'site'         => 'www.pornhubpremium.com',
                                    'billing'      => [
                                        'currency'  => 'USD',
                                        'initial'   => [
                                            'amount'             => '1.95',
                                            'valid_until_period' => 30,
                                            'valid_until_unit'   => 'DAY'
                                        ],
                                        'recurring' => [
                                            [
                                                'amount'    => '10',
                                                'frequency' => 30,
                                                'unit'      => 'DAY'
                                            ]
                                        ]
                                    ],
                                    'descriptions' => [
                                        'Initial Purchase: 1.86, Tax Name: 5%, Tax Amount: 0.09, Total Charge: 1.95',
                                        'Recurring Purchase: 9.5, Tax Name: 5%, Tax Amount: 0.5, Total Charge: 10'
                                    ]
                                ]
                            ]
                        ]
                    )
                ]
            )
        );

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'    => BillerInteraction::TYPE_RESPONSE,
                    'payload' => json_encode(
                        [
                            'success'     => true,
                            'cacheKey'    => '032fd882-6b32-4016-9f17-feed6c8bf251',
                            'redirectURL' => 'https:\/\/staging.wnu.com\/invoice?cacheKey=032fd882-6b32-4016-9f17-feed6c8bf251&version=4'
                        ]
                    )
                ]
            )
        );

        $epochResponse = [
            'email'            => $data['email'] ?? 'email@mindgeek.com',
            'name'             => $data['name'] ?? 'FirstName LastName',
            'postalcode'       => '123456',
            'zip'              => $data['zip'] ?? '123456',
            'prepaid'          => 'N',
            'country'          => $data['country'] ?? 'RO',
            'ipaddress'        => '185.80.127.252',
            'submit_count'     => '1',
            'trans_amount'     => '14.95',
            'trans_amount_usd' => '16.09',
            'trans_currency'   => 'EUR',
            'transaction_id'   => '1201415378',
            'amount'           => '16.09',
            'currency'         => 'EUR',
            'localamount'      => '14.95',
            'payment_type'     => $data['paymentType'] ?? 'CC',
            'payment_subtype'  => $data['paymentSubtype'] ?? 'VS',
            'last4'            => '9165',
            'order_id'         => '2344619085',
            'member_id'        => '2344619085',
            'pi_code'          => 'InvoiceProduct76897',
            'session_id'       => '81b28506-077e-4524-bcc2-cfcf4fd75581',
            'sessionId'        => '12345',
            'transactionId'    => '998',
            'ans'              => 'Y412081UU |2344619085',
            'event_datetime'   => '2020-05-04T09:41:57.707Z',
            'epoch_digest'     => 'a5e1865ba0cf83a92a608ae2980daa6e'
        ];

        if (isset($data['paymentType']) && $data['paymentType'] === 'PP') {
            unset($epochResponse['name'], $epochResponse['zip']);
        }

        $collection->add(
            $this->createBillerInteraction(
                [
                    'type'    => BillerInteraction::TYPE_RESPONSE,
                    'payload' => json_encode(
                        [
                            'status'        => 'approved',
                            'paymentType'   => 'cc',
                            'paymentMethod' => 'visa',
                            'type'          => 'join',
                            'request'       => '',
                            'response'      => $epochResponse
                        ]
                    )
                ]
            )
        );

        return $collection;
    }

    /**
     * @param string $biller The biller for which to create.
     *
     * @return BillerUnifiedGroupErrorResponse
     */
    protected function createBillerUnifiedGroupErrorResponse(string $biller): BillerUnifiedGroupErrorResponse
    {
        $fields = [];

        switch ($biller) {
            case RocketGateBillerSettings::ROCKETGATE:
                $fields = (new RocketgateUnifiedGroupError())
                    ->setReasonCode('reasonCode1')
                    ->setBankResponseCode('bankResponseCode1')
                    ->generateMapField();

                break;
            case NetbillingBillerSettings::NETBILLING:
                $fields = (new NetbillingUnifiedGroupError())
                    ->setProcessor('processor1')
                    ->setAuthMessage('authMessage1')
                    ->generateMapField();

                break;
        }

        $response = new BillerUnifiedGroupErrorResponse();
        $response->setMappingCriteria($fields);

        return $response;
    }

    /**
     * @return string
     */
    protected function cardHash(): string
    {
        return 'card hash';
    }

    /**
     * @return string
     */
    protected function cardDescription(): string
    {
        return 'card description';
    }
}
