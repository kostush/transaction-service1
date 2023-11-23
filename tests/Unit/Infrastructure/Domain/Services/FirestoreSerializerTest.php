<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use Google\Cloud\Core\Timestamp;
use Probiller\Common\BillerMapping;
use Probiller\Common\Fields\BillerData;
use Probiller\Common\Fields\BillerFields;
use Probiller\Rocketgate\RocketgateFields;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\AuthTransaction;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigServiceClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use Tests\UnitTestCase;

class FirestoreSerializerTest extends UnitTestCase
{
    /**
     * @var FirestoreSerializer
     */
    private $firestoreSerializer;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->firestoreSerializer = app()->make(FirestoreSerializer::class);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_rocketgate_charge_even_if_card_is_expired(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D14D1B15',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'merchantPassword'   => $this->faker->password,
                        'cvv2'               => '*******',
                        'amount'             => '1',
                        'expireMonth'        => 1,
                        'use3DSecure'        => 'FALSE',
                        'ipAddress'          => $this->faker->ipv4,
                        'version'            => 'P6.6m',
                        'cvv2Check'          => 'TRUE',
                        'cardNo'             => '*******',
                        'transactionType'    => 'CC_CONFIRM',
                        'referenceGUID'      => '1000179D14D1B15',
                        'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                        'rebillFrequency'    => 30,
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'billingType'        => 'I',
                        'rebillAmount'       => '1',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'currency'           => $this->faker->currencyCode,
                        'rebillStart'        => 5,
                        'expireYear'         => 2023
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'cardHash'           => 'hash',
                        'authNo'             => '544483',
                        'cardType'           => 'VISA',
                        'transactionTime'    => $this->faker->dateTime,
                        'cardDescription'    => 'UNKNOWN',
                        'cardLastFour'       => '1091',
                        'version'            => '1.0',
                        'guidNo'             => '1000179D14D1B15',
                        'responseCode'       => '0',
                        'cardDebitCredit'    => '0',
                        'payType'            => 'CREDIT',
                        'merchantAccount'    => '70',
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'scrubResults'       => 'NEGDB=0,PROFILE=0,ACTIVITY=0',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'retrievalNo'        => '1000179d14d1b15',
                        'approvedCurrency'   => $this->faker->currencyCode,
                        'approvedAmount'     => '1.0',
                        'reasonCode'         => '0',
                        'cardExpiration'     => '0121',
                        'bankResponseCode'   => '0',
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => ChargeTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => '1000179D14D1B15',
                    'type'                => 'sale',
                    'customerId'          => '417391a0-60b8a730773175.53393644',
                    'invoiceId'           => '88522c60-60b8a730773500.93439457'
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'ipAddress'          => $this->faker->ipv4,
                'merchantDescriptor' => '',
                'merchantSiteId'     => '',
                'merchantProductId'  => '',
                'merchantAccount'    => '',
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_netbilling_charge_even_if_card_is_expired(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'netbilling' => [
                    'billerMemberId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'recurringId'    => $this->faker->numberBetween(100000000000, 999999999999),
                    'transId'        => $this->faker->numberBetween(100000000000, 999999999999),
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'lastName'           => $this->faker->lastName,
                        'zipCode'            => $this->faker->postcode,
                        'country'            => $this->faker->countryCode,
                        'city'               => $this->faker->city,
                        'initialDays'        => '30',
                        'description'        => null,
                        'payType'            => 'cc',
                        'browser'            => 'browser',
                        'memberUsername'     => $this->faker->userName,
                        'host'               => '',
                        'cardExpire'         => '0521',
                        'rebillStart'        => 5,
                        'state'              => $this->faker->state,
                        'email'              => $this->faker->email,
                        'memberId'           => '',
                        'amount'             => '1',
                        'address'            => $this->faker->address,
                        'cardCvv2'           => '*******',
                        'ipAddress'          => $this->faker->ipv4,
                        'transactionId'      => $this->faker->uuid,
                        'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                        'firstName'          => $this->faker->firstName,
                        'memberPassword'     => $this->faker->password,
                        'rebillFrequency'    => '30',
                        'phone'              => $this->faker->phoneNumber,
                        'rebillAmount'       => '1',
                        'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                        'routingCode'        => '',
                        'cardNumber'         => '*******',
                        'disableFraudChecks' => false,
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'member_id'       => $this->faker->numberBetween(100000000000, 999999999999),
                        'cvv2_code'       => 'M',
                        'status_code'     => '1',
                        'recurring_id'    => $this->faker->numberBetween(100000000000, 999999999999),
                        'auth_msg'        => 'TEST APPROVED',
                        'auth_date'       => $this->faker->dateTime,
                        'trans_id'        => $this->faker->numberBetween(100000000000, 999999999999),
                        'processor'       => 'TEST',
                        'auth_code'       => '999999',
                        'settle_currency' => $this->faker->currencyCode,
                        'avs_code'        => 'X',
                        'settle_amount'   => '1.00',
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => NetbillingBillerSettings::NETBILLING,
            'type'                            => ChargeTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'type'                => 'sale',
                    'customerId'          => $this->faker->numberBetween(100000000000, 999999999999)
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => NetbillingBillerSettings::NETBILLING_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $this->faker->randomLetter,
                'billerMemberId'     => '',
                'initialDays'        => 30,
                'ipAddress'          => $this->faker->ipv4,
                'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                'browser'            => 'browser test',
                'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                'host'               => '',
                'binRouting'         => '',
                'disableFraudChecks' => false,
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => [
                    'ownerZip'     => $this->faker->postcode,
                    'ownerState'   => $this->faker->state,
                    'ownerPhoneNo' => $this->faker->phoneNumber,
                    'ownerCountry' => $this->faker->country,
                    'ownerAddress' => $this->faker->address,
                    'ownerCity'    => $this->faker->city,
                ],
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => [
                    'ownerLastName'  => $this->faker->lastName,
                    'ownerEmail'     => [
                        'email' => $this->faker->email
                    ],
                    'ownerPassword'  => $this->faker->password,
                    'ownerUserName'  => $this->faker->userName,
                    'ownerFirstName' => $this->faker->firstName,
                ],
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_netbilling_charge_even_if_card_is_expired_tsv2(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'netbilling' => [
                    'billerMemberId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'recurringId'    => $this->faker->numberBetween(100000000000, 999999999999),
                    'transId'        => $this->faker->numberBetween(100000000000, 999999999999),
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'lastName'           => $this->faker->lastName,
                        'zipCode'            => $this->faker->postcode,
                        'country'            => $this->faker->countryCode,
                        'city'               => $this->faker->city,
                        'initialDays'        => '30',
                        'description'        => null,
                        'payType'            => 'cc',
                        'browser'            => 'browser',
                        'memberUsername'     => $this->faker->userName,
                        'host'               => '',
                        'cardExpire'         => '0521',
                        'rebillStart'        => 5,
                        'state'              => $this->faker->state,
                        'email'              => $this->faker->email,
                        'memberId'           => '',
                        'amount'             => '1',
                        'address'            => $this->faker->address,
                        'cardCvv2'           => '*******',
                        'ipAddress'          => $this->faker->ipv4,
                        'transactionId'      => $this->faker->uuid,
                        'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                        'firstName'          => $this->faker->firstName,
                        'memberPassword'     => $this->faker->password,
                        'rebillFrequency'    => '30',
                        'phone'              => $this->faker->phoneNumber,
                        'rebillAmount'       => '1',
                        'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                        'routingCode'        => '',
                        'cardNumber'         => '*******',
                        'disableFraudChecks' => false,
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'member_id'       => $this->faker->numberBetween(100000000000, 999999999999),
                        'cvv2_code'       => 'M',
                        'status_code'     => '1',
                        'recurring_id'    => $this->faker->numberBetween(100000000000, 999999999999),
                        'auth_msg'        => 'TEST APPROVED',
                        'auth_date'       => $this->faker->dateTime,
                        'trans_id'        => $this->faker->numberBetween(100000000000, 999999999999),
                        'processor'       => 'TEST',
                        'auth_code'       => '999999',
                        'settle_currency' => $this->faker->currencyCode,
                        'avs_code'        => 'X',
                        'settle_amount'   => '1.00',
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => NetbillingBillerSettings::NETBILLING,
            'type'                            => ChargeTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'type'                => 'sale',
                    'customerId'          => $this->faker->numberBetween(100000000000, 999999999999)
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => NetbillingBillerSettings::NETBILLING_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $this->faker->randomLetter,
                'billerMemberId'     => '',
                'initialDays'        => 30,
                'ipAddress'          => $this->faker->ipv4,
                'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                'browser'            => 'browser test',
                'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                'host'               => '',
                'binRouting'         => '',
                'disableFraudChecks' => false,
            ],
            'chargeInformation'               => [
                'amounts'   => [
                    'basePrice'  => 1.0,
                    'finalPrice' => 1.0,
                    'taxes'      => 0.0,
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amounts'   => [
                        'basePrice'  => 1.0,
                        'finalPrice' => 1.0,
                        'taxes'      => 0.0,
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => [
                    'ownerZip'     => $this->faker->postcode,
                    'ownerState'   => $this->faker->state,
                    'ownerPhoneNo' => $this->faker->phoneNumber,
                    'ownerCountry' => $this->faker->country,
                    'ownerAddress' => $this->faker->address,
                    'ownerCity'    => $this->faker->city,
                ],
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => [
                    'ownerLastName'  => $this->faker->lastName,
                    'ownerEmail'     => [
                        'email' => $this->faker->email
                    ],
                    'ownerPassword'  => $this->faker->password,
                    'ownerUserName'  => $this->faker->userName,
                    'ownerFirstName' => $this->faker->firstName,
                ],
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_rocketgate_rebill_update_even_if_card_is_expired(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D1C24CC3',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'merchantPassword'   => $this->faker->password,
                        'cvv2'               => '*******',
                        'amount'             => '1',
                        'expireMonth'        => 1,
                        'use3DSecure'        => 'FALSE',
                        'ipAddress'          => $this->faker->ipv4,
                        'version'            => 'P6.6m',
                        'cvv2Check'          => 'TRUE',
                        'cardNo'             => '*******',
                        'transactionType'    => 'CC_CONFIRM',
                        'rebillEndDate'      => 'CLEAR',
                        'referenceGUID'      => '1000179D14D1B15',
                        'billingType'        => 'R',
                        'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'rebillFrequency'    => 30,
                        'rebillAmount'       => '1',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'currency'           => $this->faker->currencyCode,
                        'rebillStart'        => 5,
                        'expireYear'         => 2021
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'cardHash'           => 'hash',
                        'lastBillingDate'    => $this->faker->date(),
                        'lastReasonCode'     => '0',
                        'transactionTime'    => $this->faker->dateTime,
                        'cardDescription'    => 'UNKNOWN',
                        'guidNo'             => '1000179D1C24CC3',
                        'responseCode'       => '0',
                        'cardDebitCredit'    => '0',
                        'rebillEndDate'      => 'CLEAR',
                        'joinDate'           => $this->faker->date(),
                        'payType'            => 'CREDIT',
                        'lastBillingAmount'  => '20.0',
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'scrubResults'       => 'NEGDB=0,PROFILE=0,ACTIVITY=0',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'retrievalNo'        => '1000179d1c24cc3',
                        'approvedCurrency'   => 'USD',
                        'approvedAmount'     => '20.0',
                        'rebillStatus'       => 'ACTIVE',
                        'reasonCode'         => '0',
                        'cardExpiration'     => '0123',
                        'joinAmount'         => '1.0',
                        'authNo'             => '248091',
                        'cardType'           => 'VISA',
                        'cardLastFour'       => '1091',
                        'version'            => '1.0',
                        'merchantSiteID'     => '0',
                        'merchantAccount'    => '70',
                        'rebillFrequency'    => '30',
                        'rebillAmount'       => '20',
                        'rebillDate'         => $this->faker->dateTime,
                        'bankResponseCode'   => '0'
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => RebillUpdateTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => '1000179D14D1B15',
                    'type'                => 'sale',
                    'customerId'          => '417391a0-60b8a730773175.53393644',
                    'invoiceId'           => '88522c60-60b8a730773500.93439457'
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'paymentInformation'              => [
                'expirationYear'           => 2023,
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 1,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ]
            ],
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => $this->faker->uuid,
            'status'                          => Approved::NAME
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, $this->createMock(Transaction::class));

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_netbilling_rebill_update_even_if_card_is_expired(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'netbilling' => [
                    'billerMemberId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'transId'        => $this->faker->numberBetween(100000000000, 999999999999),
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'lastName'        => $this->faker->lastName,
                        'zipCode'         => $this->faker->postcode,
                        'country'         => $this->faker->countryCode,
                        'city'            => $this->faker->city,
                        'initialDays'     => '30',
                        'description'     => null,
                        'payType'         => 'cc',
                        'browser'         => null,
                        'host'            => null,
                        'cardExpire'      => '0521',
                        'rebillStart'     => 5,
                        'state'           => $this->faker->state,
                        'email'           => $this->faker->email,
                        'memberId'        => $this->faker->numberBetween(100000000000, 999999999999),
                        'amount'          => '1',
                        'address'         => $this->faker->address,
                        'cardCvv2'        => '*******',
                        'ipAddress'       => null,
                        'transactionId'   => $this->faker->uuid,
                        'accountId'       => (string) $this->faker->numberBetween(100000000000, 999999999999),
                        'firstName'       => $this->faker->firstName,
                        'rebillFrequency' => '30',
                        'phone'           => $this->faker->phoneNumber,
                        'rebillAmount'    => '1',
                        'siteTag'         => $_ENV['NETBILLING_SITE_TAG'],
                        'routingCode'     => '',
                        'cardNumber'      => '*******'
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'cvv2_code'       => 'M',
                        'status_code'     => '1',
                        'auth_msg'        => 'TEST APPROVED',
                        'auth_date'       => $this->faker->dateTime,
                        'trans_id'        => $this->faker->numberBetween(100000000000, 999999999999),
                        'processor'       => 'TEST',
                        'auth_code'       => '999999',
                        'settle_currency' => 'USD',
                        'avs_code'        => 'X',
                        'settle_amount'   => '20.00'
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => NetbillingBillerSettings::NETBILLING,
            'type'                            => RebillUpdateTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => $this->faker->uuid,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'type'                => 'sale',
                    'customerId'          => null
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => NetbillingBillerSettings::NETBILLING_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'billerMemberId'     => '',
                'merchantPassword'   => $this->faker->randomLetter,
                'initialDays'        => 30,
                'ipAddress'          => null,
                'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                'browser'            => null,
                'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                'host'               => '',
                'binRouting'         => ''
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => $this->faker->uuid,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => [
                    'ownerZip'     => $this->faker->postcode,
                    'ownerState'   => $this->faker->state,
                    'ownerPhoneNo' => $this->faker->phoneNumber,
                    'ownerCountry' => $this->faker->country,
                    'ownerAddress' => $this->faker->address,
                    'ownerCity'    => $this->faker->city,
                ],
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => [
                    'ownerLastName'  => $this->faker->lastName,
                    'ownerEmail'     => [
                        'email' => $this->faker->email
                    ],
                    'ownerPassword'  => $this->faker->password,
                    'ownerUserName'  => $this->faker->userName,
                    'ownerFirstName' => $this->faker->firstName,
                ],
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ],
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, $this->createMock(Transaction::class));

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_rocketgate_rebill_update_even_if_card_is_expired_empty_billerInteraction(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D1C24CC3',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractions'              => null,
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => RebillUpdateTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => '1000179D14D1B15',
                    'type'                => 'sale',
                    'customerId'          => '417391a0-60b8a730773175.53393644',
                    'invoiceId'           => '88522c60-60b8a730773500.93439457'
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'paymentInformation'              => [
                'expirationYear'           => 2023,
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 1,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ]
            ],
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => $this->faker->uuid,
            'status'                          => Approved::NAME
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, $this->createMock(Transaction::class));

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     */
    public function it_should_return_transaction_entity_for_pumapay()
    {
        $data = [
            'billerChargeSettings' => [
                'apiKey'        => $this->faker->uuid,
                'businessId'    => $this->faker->uuid,
                'businessModel' => $this->faker->uuid,
                'description'   => "Membership to pornhubpremium.com for 1 day for a charge of $0.30",
                'title'         => "Pumapay TEST Recc only(do not use)"
            ],
            'billerId'             => "12345",
            'billerInteractions'   => '[{"type":"request","payload":{"currency":"USD","title":"Pumapay TEST Recc only(do not use)","description":"Membership to pornhubpremium.com for 1 day for a charge of $0.30","frequency":86400,"trialPeriod":86400,"numberOfPayments":60,"typeID":6,"amount":40,"initialPaymentAmount":30},"createdAt":{"date":"2021-08-26 19:06:13.096964","timezone_type":3,"timezone":"UTC"}},{"type":"response","payload":{"success":true,"status":200,"message":"Successfully retrieved the QR code.","data":{"encryptText":"ENCRYPT_TEXT","qrImage":"QR_CODE_IMAGE"}},"createdAt":{"date":"2021-08-26 19:06:13.903824","timezone_type":3,"timezone":"UTC"}}]',
            'billerName'           => "pumapay",
            'billerTransactions'   => null,
            'chargeId'             => "00000000-0000-0000-0000-000000000000",
            'chargeInformation'    => [
                'amount'   => ['value' => 0.3],
                'currency' => ['code' => "USD"],
                'rebill'   => [
                    'amount'    => ['value' => 0.4],
                    'frequency' => 1,
                    'start'     => 1
                ]
            ],

            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'isNsf'                           => false,
            'isPrimaryCharge'                 => true,
            'legacyMemberId'                  => null,
            'legacySubscriptionId'            => null,
            'legacyTransactionId'             => null,
            'originalTransactionId'           => null,
            'paymentInformation'              => null,
            'paymentMethod'                   => null,
            'paymentType'                     => "crypto",
            'previousTransactionId'           => null,
            'siteId'                          => "299d3e6b-cf3d-11e9-8c91-0cc47a283dd2",
            'siteName'                        => null,
            'status'                          => "pending",
            'subsequentOperationFields'       => null,
            'subsequentOperationFieldsLegacy' => null,
            'threedsVersion'                  => 0,
            'transactionId'                   => "e237f22f-ea04-4ae7-ad95-e17100e34f1b",
            'type'                            => "charge",
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'version'                         => 1
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, $this->createMock(Transaction::class));

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(BillerSettings::PUMAPAY, $transaction->billerName());
    }

    /**
     * @test
     * @return Transaction
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_call_config_service_if_missing_merchant_info_on_transaction_for_rocketgate(): Transaction
    {
        $merchantPassword = 'MerchantPassword_for_test';

        $rocketgateFields = $this->createMock(RocketgateFields::class);
        $rocketgateFields->method('getMerchantPassword')
            ->willReturn($merchantPassword);

        $billerFields = $this->createMock(BillerFields::class);
        $billerFields->method('getRocketgate')
            ->willReturn($rocketgateFields);

        $billerData = $this->createMock(BillerData::class);
        $billerData->method('getBillerFields')
            ->willReturn($billerFields);

        $billerMapping = $this->createMock(BillerMapping::class);
        $billerMapping->method('getBiller')
            ->willReturn($billerData);

        $configServiceClient = $this->createMock(ConfigServiceClient::class);
        $configServiceClient->expects($this->once())
            ->method('retrieveRocketgateBillerMapping')
            ->willReturn($billerMapping);

        $firestoreSerializer = new FirestoreSerializer($configServiceClient);

        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D1C24CC3',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractions'              => null,
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => RebillUpdateTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => '1000179D14D1B15',
                    'type'                => 'sale',
                    'customerId'          => '417391a0-60b8a730773175.53393644',
                    'invoiceId'           => '88522c60-60b8a730773500.93439457'
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'paymentInformation'              => [
                'expirationYear'           => 202023,
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 1,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ]
            ],
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => null,
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_5'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => $this->faker->uuid,
            'status'                          => Approved::NAME
        ];

        $transaction = $firestoreSerializer->hydrate($data, $this->createMock(Transaction::class));

        $this->assertEquals($merchantPassword,$transaction->billerChargeSettings()->toArray()['merchantPassword']);

        return $transaction;
    }

    /**
     * @test
     * @depends it_should_call_config_service_if_missing_merchant_info_on_transaction_for_rocketgate
     * @param Transaction $transaction
     */
    public function it_should_convert_the_expiration_year_to_an_acceptable_form(Transaction $transaction): void
    {
        $this->assertEquals('2023', $transaction->paymentInformation()->toArray()['expirationYear']);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_rocketgate_auth_with_empty_amount(): void
    {
        $data = [
            'legacyTransactionId'             => '1234',
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D14D1B15',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'merchantPassword'   => $this->faker->password,
                        'cvv2'               => '*******',
                        'amount'             => '1',
                        'expireMonth'        => 1,
                        'use3DSecure'        => 'FALSE',
                        'ipAddress'          => $this->faker->ipv4,
                        'version'            => 'P6.6m',
                        'cvv2Check'          => 'TRUE',
                        'cardNo'             => '*******',
                        'transactionType'    => 'CC_CONFIRM',
                        'referenceGUID'      => '1000179D14D1B15',
                        'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                        'rebillFrequency'    => 30,
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'billingType'        => 'I',
                        'rebillAmount'       => '1',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'currency'           => $this->faker->currencyCode,
                        'rebillStart'        => 5,
                        'expireYear'         => 2023
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'cardHash'           => 'hash',
                        'authNo'             => '544483',
                        'cardType'           => 'VISA',
                        'transactionTime'    => $this->faker->dateTime,
                        'cardDescription'    => 'UNKNOWN',
                        'cardLastFour'       => '1091',
                        'version'            => '1.0',
                        'guidNo'             => '1000179D14D1B15',
                        'responseCode'       => '0',
                        'cardDebitCredit'    => '0',
                        'payType'            => 'CREDIT',
                        'merchantAccount'    => '70',
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'scrubResults'       => 'NEGDB=0,PROFILE=0,ACTIVITY=0',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'retrievalNo'        => '1000179d14d1b15',
                        'approvedCurrency'   => $this->faker->currencyCode,
                        'approvedAmount'     => '1.0',
                        'reasonCode'         => '0',
                        'cardExpiration'     => '0121',
                        'bankResponseCode'   => '0',
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => AuthTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerId' => 1,
                    'status'   => 'Ok',
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'ipAddress'          => $this->faker->ipv4,
                'merchantDescriptor' => '',
                'merchantSiteId'     => '',
                'merchantProductId'  => '',
                'merchantAccount'    => '',
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_netbilling_auth_and_empty_amount(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'netbilling' => [
                    'billerMemberId' => $this->faker->numberBetween(100000000000, 999999999999),
                    'recurringId'    => $this->faker->numberBetween(100000000000, 999999999999),
                    'transId'        => $this->faker->numberBetween(100000000000, 999999999999),
                ]
            ],
            'billerInteractions'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'lastName'           => $this->faker->lastName,
                        'zipCode'            => $this->faker->postcode,
                        'country'            => $this->faker->countryCode,
                        'city'               => $this->faker->city,
                        'initialDays'        => '30',
                        'description'        => null,
                        'payType'            => 'cc',
                        'browser'            => 'browser',
                        'memberUsername'     => $this->faker->userName,
                        'host'               => '',
                        'cardExpire'         => $_ENV['NETBILLING_CARD_EXP_MONTH_YEAR'],
                        'rebillStart'        => 5,
                        'state'              => $this->faker->state,
                        'email'              => $this->faker->email,
                        'memberId'           => '',
                        'amount'             => '1',
                        'address'            => $this->faker->address,
                        'cardCvv2'           => '*******',
                        'ipAddress'          => $this->faker->ipv4,
                        'transactionId'      => $this->faker->uuid,
                        'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                        'firstName'          => $this->faker->firstName,
                        'memberPassword'     => $this->faker->password,
                        'rebillFrequency'    => '30',
                        'phone'              => $this->faker->phoneNumber,
                        'rebillAmount'       => '1',
                        'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                        'routingCode'        => '',
                        'cardNumber'         => '*******',
                        'disableFraudChecks' => false,
                    ]
                ],
                [
                    'type'      => 'response',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => [
                        'member_id'       => $this->faker->numberBetween(100000000000, 999999999999),
                        'cvv2_code'       => 'M',
                        'status_code'     => '1',
                        'recurring_id'    => $this->faker->numberBetween(100000000000, 999999999999),
                        'auth_msg'        => 'TEST APPROVED',
                        'auth_date'       => $this->faker->dateTime,
                        'trans_id'        => $this->faker->numberBetween(100000000000, 999999999999),
                        'processor'       => 'TEST',
                        'auth_code'       => '999999',
                        'settle_currency' => $this->faker->currencyCode,
                        'avs_code'        => 'X',
                        'settle_amount'   => '1.00',
                    ]
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => NetbillingBillerSettings::NETBILLING,
            'type'                            => AuthTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerId' => 1,
                    'status'   => 'Ok',
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => NetbillingBillerSettings::NETBILLING_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $this->faker->randomLetter,
                'billerMemberId'     => '',
                'initialDays'        => 30,
                'ipAddress'          => $this->faker->ipv4,
                'accountId'          => (string) $this->faker->numberBetween(100000000000, 999999999999),
                'browser'            => 'browser test',
                'siteTag'            => $_ENV['NETBILLING_SITE_TAG'],
                'host'               => '',
                'binRouting'         => '',
                'disableFraudChecks' => false,
            ],
            'chargeInformation'               => [
                'amount'   => [],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => [
                    'ownerZip'     => $this->faker->postcode,
                    'ownerState'   => $this->faker->state,
                    'ownerPhoneNo' => $this->faker->phoneNumber,
                    'ownerCountry' => $this->faker->country,
                    'ownerAddress' => $this->faker->address,
                    'ownerCity'    => $this->faker->city,
                ],
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => [
                    'ownerLastName'  => $this->faker->lastName,
                    'ownerEmail'     => [
                        'email' => $this->faker->email
                    ],
                    'ownerPassword'  => $this->faker->password,
                    'ownerUserName'  => $this->faker->userName,
                    'ownerFirstName' => $this->faker->firstName,
                ],
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);

        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function it_should_return_transaction_entity_for_rocketgate_charge_with_interaction_history(): void
    {
        $data = [
            'legacyTransactionId'             => null,
            'subsequentOperationFields'       => [
                'rocketgate' => [
                    'referenceGuid'      => '1000179D14D1B15',
                    'merchantAccount'    => '70',
                    'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                    'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
                ]
            ],
            'billerInteractionsHistory'              => [
                [
                    'type'      => 'request',
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'billerInteractionId' => $this->faker->uuid,
                    'payload'   => json_encode([
                        'merchantPassword'   => $this->faker->password,
                        'cvv2'               => '*******',
                        'amount'             => '1',
                        'expireMonth'        => 1,
                        'use3DSecure'        => 'FALSE',
                        'ipAddress'          => $this->faker->ipv4,
                        'version'            => 'P6.6m',
                        'cvv2Check'          => 'TRUE',
                        'cardNo'             => '*******',
                        'transactionType'    => 'CC_CONFIRM',
                        'referenceGUID'      => '1000179D14D1B15',
                        'merchantID'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                        'rebillFrequency'    => 30,
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'billingType'        => 'I',
                        'rebillAmount'       => '1',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'currency'           => $this->faker->currencyCode,
                        'rebillStart'        => 5,
                        'expireYear'         => 2023
                    ])
                ],
                [
                    'type'      => 'response',
                    'billerInteractionId' => $this->faker->uuid,
                    'createdAt' => new Timestamp(new \DateTimeImmutable()),
                    'payload'   => json_encode([
                        'cardHash'           => 'hash',
                        'authNo'             => '544483',
                        'cardType'           => 'VISA',
                        'transactionTime'    => $this->faker->dateTime,
                        'cardDescription'    => 'UNKNOWN',
                        'cardLastFour'       => '1091',
                        'version'            => '1.0',
                        'guidNo'             => '1000179D14D1B15',
                        'responseCode'       => '0',
                        'cardDebitCredit'    => '0',
                        'payType'            => 'CREDIT',
                        'merchantAccount'    => '70',
                        'merchantInvoiceID'  => '88522c60-60b8a730773500.93439457',
                        'scrubResults'       => 'NEGDB=0,PROFILE=0,ACTIVITY=0',
                        'merchantCustomerID' => '417391a0-60b8a730773175.53393644',
                        'retrievalNo'        => '1000179d14d1b15',
                        'approvedCurrency'   => $this->faker->currencyCode,
                        'approvedAmount'     => '1.0',
                        'reasonCode'         => '0',
                        'cardExpiration'     => '0121',
                        'bankResponseCode'   => '0',
                    ])
                ]
            ],
            'siteName'                        => null,
            'isNsf'                           => false,
            'billerName'                      => RocketGateChargeSettings::ROCKETGATE,
            'type'                            => ChargeTransaction::TYPE,
            'isPrimaryCharge'                 => true,
            'paymentType'                     => 'cc',
            'createdAt'                       => new Timestamp(new \DateTimeImmutable()),
            'originalTransactionId'           => null,
            'billerTransactions'              => [
                [
                    'billerTransactionId' => '1000179D14D1B15',
                    'type'                => 'sale',
                    'customerId'          => '417391a0-60b8a730773175.53393644',
                    'invoiceId'           => '88522c60-60b8a730773500.93439457'
                ]
            ],
            'subsequentOperationFieldsLegacy' => null,
            'legacySubscriptionId'            => null,
            'updatedAt'                       => new Timestamp(new \DateTimeImmutable()),
            'billerId'                        => RocketGateChargeSettings::ROCKETGATE_ID,
            'version'                         => 1,
            'transactionId'                   => $this->faker->uuid,
            'billerChargeSettings'            => [
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_1'],
                'ipAddress'          => $this->faker->ipv4,
                'merchantDescriptor' => '',
                'merchantSiteId'     => '',
                'merchantProductId'  => '',
                'merchantAccount'    => '',
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_1'],
                'merchantInvoiceId'  => '88522c60-60b8a730773500.93439457',
                'merchantCustomerId' => '417391a0-60b8a730773175.53393644'
            ],
            'chargeInformation'               => [
                'amount'   => [
                    'value' => 1.0
                ],
                'currency' => [
                    'code' => $this->faker->currencyCode
                ],
                'rebill'   => [
                    'amount'    => [
                        'value' => 1.0
                    ],
                    'start'     => 5,
                    'frequency' => 30
                ]
            ],
            'threedsVersion'                  => 0,
            'chargeId'                        => $this->faker->uuid,
            'siteId'                          => $this->faker->uuid,
            'paymentMethod'                   => null,
            'legacyMemberId'                  => null,
            'previousTransactionId'           => null,
            'status'                          => Approved::NAME,
            'paymentInformation'              => [
                'cvv'                      => '*******',
                'creditCardBillingAddress' => null,
                'expirationMonth'          => 5,
                'cvv2Check'                => true,
                'creditCardOwner'          => null,
                'creditCardNumber'         => [
                    'lastFour'      => substr(env('NETBILLING_CARD_NUMBER_2'), -4),
                    'isValidNumber' => 1,
                    'cardType'      => 'visa',
                    'firstSix'      => substr(env('NETBILLING_CARD_NUMBER_2'), 0, 6),
                    'cardNumber'    => '*******'
                ],
                'expirationYear'           => 2021
            ]
        ];

        $transaction = $this->firestoreSerializer->hydrate($data, null);


        $this->assertInstanceOf(Transaction::class, $transaction);

        $this->assertNotEmpty($transaction->billerInteractions()->toArray());
    }
}
