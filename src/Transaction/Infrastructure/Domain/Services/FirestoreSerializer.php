<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Money\Exception\UnknownCurrencyException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\OtherPaymentTypeInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill as TransactionRebill;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateExistingCreditCardBillerFields;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateUpdateRebillBillerFields;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\AuthTransaction;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerMember;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardBillingAddress;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardNumber;
use ProBillerNG\Transaction\Domain\Model\CreditCardOwner;
use ProBillerNG\Transaction\Domain\Model\Currency;
use ProBillerNG\Transaction\Domain\Model\Email;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebillUpdateSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateCardHash;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigServiceClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Pumapay\PumapayFirestoreSerializer;

class FirestoreSerializer
{
    /** @var ConfigServiceClient  */
    protected $configServiceClient;

    /**
     * FirestoreSerializer constructor.
     *
     * @param ConfigServiceClient $configServiceClient
     */
    public function __construct(ConfigServiceClient $configServiceClient)
    {
        $this->configServiceClient = $configServiceClient;
    }

    /**
     * @param array|null       $data                Data
     * @param Transaction|null $previousTransaction Previous transaction
     * @return Transaction|null
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws InvalidPaymentMethodException
     * @throws InvalidTransactionInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     */
    public function hydrate(?array $data, ?Transaction $previousTransaction): ?Transaction
    {
        // Need to encode as json the information about card number for deserializing purposes
        if (isset($data['paymentInformation']['creditCardNumber'])) {
            $data['paymentInformation']['creditCardNumber']['cardNumber'] = json_encode($data['paymentInformation']['creditCardNumber']);
        }

        if (!isset($data['chargeInformation']['amount']['value'])
            && isset($data['chargeInformation']['amounts']['finalPrice'])
        ) {
            $data['chargeInformation']['amount']['value'] = $data['chargeInformation']['amounts']['finalPrice'];
        }

        if (!isset($data['chargeInformation']['rebill']['amount']['value'])
            && isset($data['chargeInformation']['rebill']['amounts']['finalPrice'])
        ) {
            $data['chargeInformation']['rebill']['amount']['value'] = $data['chargeInformation']['rebill']['amounts']['finalPrice'];
        }

        $this->fromHistoryToJsonInteractions($data);

        $transaction = null;
        $billerName  = !empty($data['billerName']) ? $data['billerName'] : '';

        switch ($billerName) {
            case BillerSettings::ROCKETGATE:
                $transaction = $this->createRocketgateTransaction($data, $previousTransaction);
                break;
            case BillerSettings::NETBILLING:
                $transaction = $this->createNetbillingTransaction($data, $previousTransaction);
                break;
            case BillerSettings::QYSSO:
                $transaction = $this->createQyssoTransaction($data, $previousTransaction);
                break;
            case BillerSettings::LEGACY:
                $transaction = $this->createLegacyTransaction($data);
                break;
            case BillerSettings::PUMAPAY:
                $transaction = PumapayFirestoreSerializer::createTransaction($data);
                break;
            default:
                return null;
        }

        $this->addCommonMandatoryInformationToTransaction($transaction, $data);

        return $transaction;
    }

    /**
     * @param Transaction|null $transaction Transaction
     * @param array|null       $data        Data
     *
     * @return void
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidTransactionInformationException*
     * @throws \Exception
     * @throws InvalidBillerInteractionPayloadException
     */
    private function addCommonMandatoryInformationToTransaction(?Transaction $transaction, ?array $data): void
    {
        if ($transaction === null || empty($data)) {
            return;
        }

        $transaction->setTransactionId($data['transactionId']);
        $transaction->setStatus($data['status']);
        $transaction->setCreatedAt($data['createdAt']);
        $transaction->setUpdatedAt($data['updatedAt']);
        $transaction->setThreeDSVersion($data['threedsVersion']);
        $transaction->setLegacyMemberId($data['legacyMemberId']);
        $transaction->setLegacySubscriptionId($data['legacySubscriptionId']);
        $transaction->setLegacyTransactionId($data['legacyTransactionId']);
        $transaction->setSubsequentOperationFields(json_encode($data['subsequentOperationFields']));
        $transaction->setBillerTransactions(json_encode($data['billerTransactions']));

        $billerInteractions = $data['billerInteractions'];
        if (!empty($billerInteractions)) {
            if (!is_array($billerInteractions)) {
                $billerInteractions = json_decode($billerInteractions, true, 512, JSON_THROW_ON_ERROR);
            }

            foreach ($billerInteractions as $billerInteraction) {
                if (empty($billerInteraction['createdAt'])) {
                    $dateTimeImmutable = $transaction->createdAt();
                } else {
                    if (is_array($billerInteraction['createdAt'])) {
                        $dateTimeImmutable = DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s.u',
                            $billerInteraction['createdAt']['date']
                        );
                    } else {
                        $dateTimeImmutable = DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s.u',
                            $billerInteraction['createdAt']->get()->format('Y-m-d H:i:s.u')
                        );
                    }
                }

                /*We do this in order to support the user sync flow, in which we need to retrieve an approved RG transaction
                that doesn't have the reasonCode param in the response biller interaction payload.*/
                if ($data['billerName'] == BillerSettings::ROCKETGATE && $billerInteraction['type'] == BillerInteraction::TYPE_RESPONSE) {
                    if (!isset($billerInteraction['payload']['reasonCode'])
                        && isset($billerInteraction['payload']['responseCode'])
                        && (int) $billerInteraction['payload']['responseCode'] == 0) {
                        $billerInteraction['payload']['reasonCode'] = $billerInteraction['payload']['responseCode'];
                    }
                }

                $transaction->addBillerInteraction(
                    BillerInteraction::create(
                        $billerInteraction['type'],
                        json_encode($billerInteraction['payload']),
                        $dateTimeImmutable
                    )
                );
            }
        }
    }

    /**
     * @param array $data Data.
     * @return array
     */
    protected function convertData(array $data): array
    {
        if (!isset($data['returnUrl'])) {
            $data['returnUrl'] = null;
        }

        return $data;
    }

    /**
     * @param array            $data                Transaction data.
     * @param Transaction|null $previousTransaction Previous transaction.
     * @return Transaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPaymentMethodException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws \Exception
     */
    private function createRocketgateTransaction(array $data, ?Transaction $previousTransaction): Transaction
    {
        $data = $this->convertData($data);

        switch ($data['type']) {
            case ChargeTransaction::TYPE:
                return ChargeTransaction::createTransactionOnRocketgate(
                    $data['billerName'],
                    $this->createRocketgateBillerFieldsSettings($data),
                    $data['siteId'] ?? null,
                    $this->createPayment($data),
                    $this->createChargeInformation($data),
                    $previousTransaction,
                    !empty($data['threedsVersion']),
                    $data['returnUrl'],
                    false
                );
            case AuthTransaction::TYPE:
                return AuthTransaction::createTransactionOnRocketgate(
                    $data['billerName'],
                    $this->createRocketgateBillerFieldsSettings($data),
                    $data['siteId'] ?? null,
                    $this->createPayment($data),
                    $this->createChargeInformation($data),
                    $previousTransaction,
                    !empty($data['threedsVersion']),
                    $data['returnUrl'],
                    false
                );

            case RebillUpdateTransaction::TYPE:
                if ($data['paymentInformation'] !== null
                    && $data['chargeInformation'] !== null
                    && $data['paymentType'] !== null
                ) {
                    return RebillUpdateTransaction::createUpdateRebillTransaction(
                        $previousTransaction,
                        $data['billerName'],
                        $this->createRocketgateBillerFieldsSettings($data),
                        $this->createPaymentInformation($data),
                        $this->createChargeInformation($data),
                        $data['paymentType']
                    );
                }

                return RebillUpdateTransaction::createCancelRebillTransaction(
                    $previousTransaction,
                    $data['billerName'],
                    $this->createRocketgateBillerFieldsSettings($data)
                );
        }
    }


    /**
     * @param array            $data                Transaction data.
     * @param Transaction|null $previousTransaction Previous transaction.
     * @return Transaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws InvalidPaymentMethodException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws \Exception
     */
    private function createNetbillingTransaction(array $data, ?Transaction $previousTransaction): Transaction
    {
        $billerChargeSettings = $data['billerChargeSettings'];
        $siteTag              = $billerChargeSettings['siteTag'];
        $accountId            = $billerChargeSettings['accountId'];
        $merchantPassword     = $billerChargeSettings['merchantPassword'];
        $initialDays          = $billerChargeSettings['initialDays'] ?? null;
        $ipAddress            = $billerChargeSettings['ipAddress'] ?? null;
        $browser              = $billerChargeSettings['browser'] ?? null;
        $host                 = $billerChargeSettings['host'] ?? null;
        $description          = $billerChargeSettings['description'] ?? null;
        $binRouting           = $billerChargeSettings['binRouting'] ?? null;
        $billerMemberId       = $billerChargeSettings['billerMemberId'] ?? null;
        $disableFraudChecks   = $billerChargeSettings['disableFraudChecks'] ?? false;

        switch ($data['type']) {
            case AuthTransaction::TYPE:
                return AuthTransaction::createTransactionOnNetbilling(
                    $data['billerName'],
                    NetbillingChargeSettings::create(
                        $siteTag ?? null,
                        $accountId ?? null,
                        $merchantPassword ?? null,
                        (int) $initialDays,
                        $ipAddress,
                        $browser,
                        $host,
                        $description,
                        $binRouting,
                        $billerMemberId ?? null,
                        $disableFraudChecks
                    ),
                    $data['siteId'] ?? null,
                    $this->createPayment($data),
                    $this->createChargeInformation($data),
                    $previousTransaction,
                    BillerMember::create(
                        !empty($data['paymentInformation']['creditCardOwner']['ownerUserName']) ? $data['paymentInformation']['creditCardOwner']['ownerUserName'] : null,
                        !empty($data['paymentInformation']['creditCardOwner']['ownerPassword']) ? $data['paymentInformation']['creditCardOwner']['ownerPassword'] : null
                    ),
                    false
                );
            case ChargeTransaction::TYPE:
                return ChargeTransaction::createTransactionOnNetbilling(
                    $data['billerName'],
                    NetbillingChargeSettings::create(
                        $siteTag ?? null,
                        $accountId ?? null,
                        $merchantPassword ?? null,
                        (int) $initialDays,
                        $ipAddress,
                        $browser,
                        $host,
                        $description,
                        $binRouting,
                        $billerMemberId ?? null,
                        $disableFraudChecks
                    ),
                    $data['siteId'] ?? null,
                    $this->createPayment($data),
                    $this->createChargeInformation($data),
                    $previousTransaction,
                    BillerMember::create(
                        !empty($data['paymentInformation']['creditCardOwner']['ownerUserName']) ? $data['paymentInformation']['creditCardOwner']['ownerUserName'] : null,
                        !empty($data['paymentInformation']['creditCardOwner']['ownerPassword']) ? $data['paymentInformation']['creditCardOwner']['ownerPassword'] : null
                    ),
                    false
                );
            case RebillUpdateTransaction::TYPE:
                if ($data['chargeInformation'] !== null
                    && $data['paymentInformation'] !== null
                    && $data['paymentType'] !== null
                ) {
                    $responseBillerInteractions = self::getBillerInteractionsByType(
                        $data['billerInteractions'],
                        BillerInteraction::TYPE_RESPONSE
                    );

                    $lastBillerResponse = end($responseBillerInteractions)['payload'];

                    $data['billerInteractionCurrency'] = !empty($lastBillerResponse['settle_currency']) ? $lastBillerResponse['settle_currency'] : null;

                    return RebillUpdateTransaction::createNetbillingUpdateRebillTransaction(
                        $previousTransaction,
                        $data['billerName'],
                        NetbillingRebillUpdateSettings::create(
                            $siteTag,
                            $accountId,
                            $billerMemberId,
                            $merchantPassword,
                            $initialDays,
                            $binRouting,
                            $ipAddress,
                            $browser,
                            $host,
                            $description
                        ),
                        $this->createPaymentInformation($data),
                        $this->createChargeInformation($data),
                        $data['paymentType']
                    );
                }

                return RebillUpdateTransaction::createNetbillingCancelRebillTransaction(
                    $previousTransaction,
                    $data['billerName'],
                    NetbillingRebillUpdateSettings::create(
                        $siteTag,
                        $accountId,
                        $billerMemberId,
                        $merchantPassword,
                        $initialDays,
                        $binRouting,
                        $ipAddress,
                        $browser,
                        $host,
                        $description
                    )
                );
        }
    }

    /**
     * @param array            $data                Transaction data.
     * @param Transaction|null $previousTransaction Previous transaction
     * @return Transaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    private function createQyssoTransaction(array $data, ?Transaction $previousTransaction): Transaction
    {
        switch ($data['type']) {
            case ChargeTransaction::TYPE:
                if (empty($data['chargeInformation']['rebill'])) {
                    return ChargeTransaction::createSingleChargeOnEpoch(
                        $data['siteId'],
                        isset($data['siteName']) ? $data['siteName'] : '',
                        $data['billerName'],
                        $data['chargeInformation']['amount']['value'],
                        $data['chargeInformation']['currency']['code'],
                        $data['paymentType'],
                        $data['paymentMethod'],
                        QyssoBillerSettings::create(
                            $data['billerChargeSettings']['companyNum'],
                            $data['billerChargeSettings']['personalHashKey'],
                            $data['billerChargeSettings']['notificationUrl'],
                            $data['billerChargeSettings']['redirectUrl']
                        ),
                        !empty($data['paymentInformation']['creditCardOwner']['ownerUserName']) ? $data['paymentInformation']['creditCardOwner']['ownerUserName'] : null,
                        !empty($data['paymentInformation']['creditCardOwner']['ownerPassword']) ? $data['paymentInformation']['creditCardOwner']['ownerPassword'] : null
                    );
                }

                return ChargeTransaction::createWithRebillOnEpoch(
                    $data['siteId'],
                    isset($data['siteName']) ? $data['siteName'] : '',
                    $data['billerName'],
                    $data['chargeInformation']['amount']['value'],
                    $data['chargeInformation']['currency']['code'],
                    $data['paymentType'],
                    $data['paymentMethod'],
                    QyssoBillerSettings::create(
                        $data['billerChargeSettings']['companyNum'],
                        $data['billerChargeSettings']['personalHashKey'],
                        $data['billerChargeSettings']['notificationUrl'],
                        $data['billerChargeSettings']['redirectUrl']
                    ),
                    new TransactionRebill(
                        $data['chargeInformation']['rebill']['amount']['value'],
                        $data['chargeInformation']['rebill']['frequency'] ?? null,
                        $data['chargeInformation']['rebill']['start'] ?? null
                    ),
                    null, // TODO check if needed
                    null  // TODO check if needed
                );

            case RebillUpdateTransaction::TYPE:
                return RebillUpdateTransaction::createQyssoRebillUpdateTransaction($previousTransaction);
        }
    }


    /**
     * @param array $data Transaction data.
     * @return Transaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidTransactionTypeException
     * @throws MissingChargeInformationException
     */
    private function createLegacyTransaction(array $data): Transaction
    {
        switch ($data['type']) {
            case ChargeTransaction::TYPE:
                return ChargeTransaction::createTransactionOnLegacy(
                    $data['siteId'],
                    $data['billerName'],
                    $this->createChargeInformation($data),
                    $data['paymentType'],
                    LegacyBillerChargeSettings::create(
                        $data['billerChargeSettings']['legacyMemberId'],
                        $data['billerChargeSettings']['billerName'],
                        $data['billerChargeSettings']['returnUrl'],
                        $data['billerChargeSettings']['postbackUrl'],
                        $data['billerChargeSettings']['others']
                    ),
                    $data['paymentMethod'],
                    !empty($data['paymentInformation']['creditCardOwner']['ownerUserName']) ? $data['paymentInformation']['creditCardOwner']['ownerUserName'] : null,
                    !empty($data['paymentInformation']['creditCardOwner']['ownerPassword']) ? $data['paymentInformation']['creditCardOwner']['ownerPassword'] : null
                );
            default:
                throw new InvalidTransactionTypeException(ChargeTransaction::TYPE);
        }
    }

    /**
     * @param array $data Transaction data.
     * @return Payment
     * @throws InvalidCreditCardExpirationDateException
     * @throws Exception
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws InvalidPaymentMethodException
     */
    private function createPayment(array $data): Payment
    {
        $paymentInformation = $data['paymentInformation'];

        if (!empty($paymentInformation['creditCardNumber'])) {
            $information = new NewCreditCardInformation(
                $paymentInformation['creditCardNumber']['cardNumber'] ?? null,
                (string) $paymentInformation['expirationMonth'] ?? null,
                $this->translateExpirationYear($paymentInformation['expirationYear']) ?? null,
                $paymentInformation['cvv'] ?? null,
                $this->createMember(
                    $paymentInformation['creditCardOwner'],
                    $paymentInformation['creditCardBillingAddress']
                )
            );
        } elseif (!empty($paymentInformation['rocketgateCardHash'])) {
            $information = new ExistingCreditCardInformation($paymentInformation['rocketgateCardHash']['value']);
        } elseif (!empty($paymentInformation['rocketGateCardHash'])) {
            $information = new ExistingCreditCardInformation($paymentInformation['rocketGateCardHash']['value']);
        } elseif (!empty($paymentInformation['netbillingCardHash'])) {
            $netbillingCardHash = $paymentInformation['netbillingCardHash']['value'];

            if (preg_match('/^CS\:\\d{1,12}\:\\d{4}$/', $netbillingCardHash)) {
                $netbillingCardHash = base64_encode($netbillingCardHash);
            }

            $information = new ExistingCreditCardInformation($netbillingCardHash);
        } else {
            $information = new OtherPaymentTypeInformation(
                $paymentInformation['routingNumber'],
                $paymentInformation['accountNumber'],
                $paymentInformation['savingAccount'],
                $paymentInformation['socialSecurityLast4'],
                $this->createMember(
                    $paymentInformation['accountOwner'],
                    $paymentInformation['customerBillingAddress']
                ),
                $data['paymentType'] ?? null
            );
        }

        return new Payment(
            $data['paymentType'] ?? null,
            $information
        );
    }

    /**
     * @param array $data Transaction data.
     * @return PaymentInformation
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws Exception
     */
    private function createPaymentInformation(array $data): PaymentInformation
    {
        $paymentInformation = $data['paymentInformation'];

        if (!empty($paymentInformation['creditCardNumber'])) {
            $creditCardOwner          = $paymentInformation['creditCardOwner'];
            $creditCardBillingAddress = $paymentInformation['creditCardBillingAddress'];

            $information = CreditCardInformation::create(
                $paymentInformation['cvv2Check'],
                CreditCardNumber::create($paymentInformation['creditCardNumber']['cardNumber'] ?? null),
                CreditCardOwner::create(
                    $creditCardOwner['ownerFirstName'] ?? null,
                    $creditCardOwner['ownerLastName'] ?? null,
                    !empty($creditCardOwner['ownerEmail']['email']) ? Email::create($creditCardOwner['ownerEmail']['email']) : null,
                    $creditCardOwner['ownerUserName'] ?? null,
                    $creditCardOwner['ownerPassword'] ?? null
                ),
                CreditCardBillingAddress::create(
                    $creditCardBillingAddress['ownerAddress'] ?? null,
                    $creditCardBillingAddress['ownerCity'] ?? null,
                    $creditCardBillingAddress['ownerCountry'] ?? null,
                    $creditCardBillingAddress['ownerState'] ?? null,
                    $creditCardBillingAddress['ownerZip'] ?? null,
                    $creditCardBillingAddress['ownerPhoneNo'] ?? null
                ),
                $paymentInformation['cvv'] ?? null,
                $paymentInformation['expirationMonth'],
                $this->translateExpirationYear($paymentInformation['expirationYear']),
                false
            );
        } elseif (!empty($paymentInformation['rocketgateCardHash'])) {
            $information = PaymentTemplateInformation::create(
                RocketGateCardHash::create($paymentInformation['rocketgateCardHash']['value'])
            );
        } elseif (!empty($paymentInformation['rocketGateCardHash'])) {
            $information = PaymentTemplateInformation::create(
                RocketGateCardHash::create($paymentInformation['rocketGateCardHash']['value'])
            );
        } elseif (!empty($paymentInformation['netbillingCardHash'])) {
            $information = NetbillingPaymentTemplateInformation::create(
                NetbillingCardHash::create(base64_encode($paymentInformation['netbillingCardHash']['value']))
            );
        } else {
            $information = new CheckInformation(
                $paymentInformation['routingNumber'],
                $paymentInformation['accountNumber'],
                $paymentInformation['savingAccount'],
                $paymentInformation['socialSecurityLast4'],
                $paymentInformation['accountOwner'] ?? null,
                $paymentInformation['customerBillingAddress'] ?? null,
            );
        }

        return $information;
    }

    /**
     * @param array|null $owner          Customer data.
     * @param array|null $billingAddress Customer billing address.
     * @return Member|null
     */
    private function createMember(?array $owner = null, ?array $billingAddress = null): ?Member
    {
        if (empty($owner) && empty($billingAddress)) {
            return null;
        }

        return new Member(
            $owner['ownerFirstName'] ?? null,
            $owner['ownerLastName'] ?? null,
            $owner['ownerUserName'] ?? null,
            $owner['ownerEmail']['email'] ?? null,
            $billingAddress['ownerPhoneNo'] ?? null,
            $billingAddress['ownerAddress'] ?? null,
            $billingAddress['ownerZip'] ?? null,
            $billingAddress['ownerCity'] ?? null,
            $billingAddress['ownerState'] ?? null,
            $billingAddress['ownerCountry'] ?? null,
            $owner['ownerPassword'] ?? null,
            null // only needed for epoch sec rev
        );
    }

    /**
     * @param array $data Transaction data.
     * @return BillerSettings
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws Exception
     */
    private function createRocketgateBillerFieldsSettings(array $data): BillerSettings
    {
        $billerChargeSettings = $data['billerChargeSettings'];

        $merchantCustomerId = null;
        $merchantInvoiceId  = null;

        if (!empty($billerChargeSettings['merchantCustomerId'])) {
            $merchantCustomerId = $billerChargeSettings['merchantCustomerId'];
        } elseif (!empty($data['subsequentOperationFields']['rocketgate']['merchantCustomerId'])) {
            $merchantCustomerId = $data['subsequentOperationFields']['rocketgate']['merchantCustomerId'];
        }

        if (!empty($billerChargeSettings['merchantInvoiceId'])) {
            $merchantInvoiceId = $billerChargeSettings['merchantInvoiceId'];
        } elseif (!empty($data['subsequentOperationFields']['rocketgate']['merchantInvoiceId'])) {
            $merchantInvoiceId = $data['subsequentOperationFields']['rocketgate']['merchantInvoiceId'];
        }

        $merchantId          = $billerChargeSettings['merchantId'] ?? null;
        $merchantPassword    = $billerChargeSettings['merchantPassword'] ?? null;
        $merchantAccount     = $billerChargeSettings['merchantAccount'] ?? null;
        $merchantSiteId      = $billerChargeSettings['merchantSiteId'] ?? '';
        $merchantProductId   = $billerChargeSettings['merchantProductId'] ?? null;
        $merchantDescriptor  = $billerChargeSettings['merchantDescriptor'] ?? null;
        $ipAddress           = $billerChargeSettings['ipAddress'] ?? null;
        $referringMerchantId = $billerChargeSettings['referringMerchantId'] ?? null;
        $sharedSecret        = $billerChargeSettings['sharedSecret'] ?? null;
        $simplified3DS       = $billerChargeSettings['simplified3DS'] ?? null;

        /** If we don`t have merchant password we try to get it from config service */
        if (empty($merchantPassword) || $merchantId == '1390920700') {
            Log::info("HydrateTransaction Trying to retrieve biller mapping from config service for empty merchant password");
            try {
                $chargeInformation = $this->createChargeInformation($data);

                /** Check if we got the charge information */
                if (!empty($chargeInformation->currency())) {
                    $currency = $chargeInformation->currency();
                } else {
                    /** If we couldn`t get the charge information, we`ll try to get the currency from biller interactions */
                    $billerInteraction = json_decode($data["billerInteractions"])[0];
                    $currency          = $billerInteraction['payload']['currency'];
                }
            } catch (InvalidChargeInformationException | MissingChargeInformationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new UnknownCurrencyException();
            }

            try {
                $billerMapping = $this->configServiceClient->retrieveRocketgateBillerMapping(
                    (string) $data['siteId'],
                    (string) $currency,
                    (string) $merchantId
                );

                /** The response from config service can be returned as null so we check for it here */
                if (!empty($billerMapping)) {
                    $merchantPassword = $billerMapping->getBiller()->getBillerFields()->getRocketgate()->getMerchantPassword();
                } else {
                    /** If we couldn`t get the info from config service, we`ll try to get from billerInteractions */
                    $merchantPassword = json_decode($data['billerInteractions'])[0]->payload->merchantPassword;
                }
            } catch (\Throwable $e) {
                Log::error(
                    $e->getMessage(),
                    [
                        'merchantId'       => $merchantId,
                        'merchantPassword' => $merchantPassword,
                        'siteId'           => (string) $data['siteId'],
                        'currency'         => (string) $currency,
                        'couldRetrieveFromConfigService' => !empty($billerMapping)
                    ]
                );
                throw new MissingMerchantInformationException("merchantPassword and/or wrong merchantId!");
            }
        }

        switch ($data['type']) {
            case AuthTransaction::TYPE:
            case ChargeTransaction::TYPE:
                if (!empty($data['paymentInformation']['rocketgateCardHash']) || !empty($data['paymentInformation']['rocketGateCardHash'])) {
                    return RocketGateExistingCreditCardBillerFields::create(
                        $merchantId,
                        $merchantPassword,
                        $merchantCustomerId,
                        $merchantInvoiceId,
                        $merchantAccount,
                        $merchantSiteId,
                        $merchantProductId,
                        $merchantDescriptor,
                        $ipAddress,
                        $referringMerchantId,
                        $sharedSecret,
                        $simplified3DS
                    );
                }

                return RocketGateChargeSettings::create(
                    $merchantId,
                    $merchantPassword,
                    $merchantCustomerId,
                    $merchantInvoiceId,
                    $merchantAccount,
                    $merchantSiteId,
                    $merchantProductId,
                    $merchantDescriptor,
                    $ipAddress,
                    $referringMerchantId,
                    $sharedSecret,
                    $simplified3DS
                );
            case RebillUpdateTransaction::TYPE:
                return new RocketGateUpdateRebillBillerFields(
                    $merchantId,
                    $merchantPassword,
                    $merchantCustomerId,
                    $merchantInvoiceId,
                    $merchantAccount
                );

        }
    }

    /**
     * @param array $data Transaction data.
     * @return ChargeInformation
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    private function createChargeInformation(array $data): ChargeInformation
    {
        $rebill = null;

        if (!empty($data['chargeInformation']['rebill']['frequency'])
            && !empty($data['chargeInformation']['rebill']['start'])
            && !empty($data['chargeInformation']['rebill']['amount']['value'])
        ) {
            $rebill = Rebill::create(
                (int) $data['chargeInformation']['rebill']['frequency'],
                (int) $data['chargeInformation']['rebill']['start'],
                Amount::create($data['chargeInformation']['rebill']['amount']['value'])
            );
        }

        $currency = !empty($data['chargeInformation']['currency']['code'])
            ? $data['chargeInformation']['currency']['code'] : $data['billerInteractionCurrency'];

        $amount = 0;

        if (!empty($data['chargeInformation']['amount'])) {
            $amount = $data['chargeInformation']['amount']['value'] ?? 0;
        }

        if (!empty($data['chargeInformation']['amounts'])) {
            $amount = $data['chargeInformation']['amounts']['value'] ?? 0;
        }

        return ChargeInformation::create(
            Amount::create($amount),
            $currency ? Currency::create($currency) : null,
            $rebill
        );
    }

    /**
     * @param array|string $billerInteractions All transaction biller interactions.
     * @param string       $type               Biller interaction type.
     *
     * @return array
     * @throws \JsonException
     */
    public static function getBillerInteractionsByType($billerInteractions, string $type): array
    {
        // Transactions like rebillUpdate aborted, don't have biller interactions.
        // Ex: 4c54572d-63b5-42d6-b244-101943540129 transaction on NB
        if (empty($billerInteraction)) {
            return [];
        }

        if (!is_array($billerInteractions)) {
            $billerInteractions = json_decode($billerInteractions, true, 512, JSON_THROW_ON_ERROR);
        }

        $billerInteractionsByType = [];

        foreach ($billerInteractions as $billerInteraction) {
            if ($billerInteraction['type'] == $type) {
                $billerInteractionsByType[] = $billerInteraction;
            }
        }

        return $billerInteractionsByType;
    }

    /**
     * Checks that the year is not corrupted and translates it if it is corrupted
     * Ex.:
     *  202023 => 2023
     *  20202 => 2020
     *  2020 => 2020
     *
     * @param $year
     *
     * @return int
     */
    private function translateExpirationYear($year): int
    {
        /** Conversion so no matter what we get as type we can apply changes and return as int */
        $year = (string) $year;

        /** If the year does not have exactly 4 characters and Carbon deems it invalid, we process it  */
        while (!Carbon::canBeCreatedFromFormat($year,'Y') || Str::length($year) != 4) {
            if(Str::length($year) > 4) {
                $year = Str::substr($year,1);
            }

            /**
             * Becuase Carbon deems an year made from 3 chars valid we have to check
             * and add to it so it becomes a valid year.
             */
            if (Str::length($year) < 4) {
                $year = $year . "0";
            }
        }

        return (int) $year;
    }

    /**
     * Encodes billerInteractionsHistory content to billerInteractions json
     * @throws \JsonException
     */
    private function fromHistoryToJsonInteractions(array &$data): void
    {
        if (empty($data['billerInteractionsHistory'])) {
            return;
        }

        if (!is_array($data['billerInteractionsHistory']) || count($data['billerInteractionsHistory']) < 1) {
            return;
        }

        $billerInteractions = [];
        foreach ($data['billerInteractionsHistory'] as $billerInteraction) {
            //billerInteractionsHistory encodes only the payload as json
            $billerInteraction['payload'] = json_decode($billerInteraction['payload'], true, 512,
                JSON_THROW_ON_ERROR);
            //this one is not present in billerInteractions json
            unset($billerInteraction['billerInteractionId']);
            $billerInteractions[] = $billerInteraction;
        }

        $data['billerInteractions'] = json_encode($billerInteractions, JSON_THROW_ON_ERROR);
    }
}
