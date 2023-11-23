<?php

declare(strict_types=1);

namespace Tests;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingExistingCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebillUpdateSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;

trait CreateTransactionDataForNetbilling
{
    /**
     * @param array|null $data Netbilling settings
     *
     * @return NetbillingChargeSettings
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws LoggerException
     */
    protected function createNetbillingChargeSettings(array $data = null): NetbillingChargeSettings
    {
        return NetbillingChargeSettings::create(
            $data['siteTag'] ?? $_ENV['NETBILLING_SITE_TAG_2'],
            $data['accountId'] ?? $_ENV['NETBILLING_ACCOUNT_ID_2'],
            $data['merchantPassword'] ?? $_ENV['NETBILLING_MERCHANT_PASSWORD'],
            $data['initialDays'] ?? 2,
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['browser'] ?? 'Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/77.0.3865.120 Safari\/537.36',
            $data['host'] ?? 'yuluserpool3.nat.as55222.com',
            $data['description'] ?? 'test description',
            $data['binRouting'] ?? 'INTC/12345'
        );
    }

    /**
     * @param array|null $data Override data
     * @return BillerLoginInfo
     */
    protected function createBillerLogin(array $data = null): BillerLoginInfo
    {
        return new BillerLoginInfo(
            $data['userName'] ?? $this->faker->userName,
            $data['password'] ?? $this->faker->password,
        );
    }

    /**
     * @param array|null $data Override Data
     * @return PerformNetbillingNewCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \Exception
     */
    protected function createPerformNetbillingNewCreditCardSaleCommandWithRebill(
        array $data = null
    ): PerformNetbillingNewCreditCardSaleCommand {
        return new PerformNetbillingNewCreditCardSaleCommand(
            isset($data) && array_key_exists('siteId', $data) ? $data['siteId'] : $this->faker->uuid,
            isset($data) && array_key_exists('amount', $data) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            isset($data) && array_key_exists('currency', $data) ? $data['currency'] : 'USD',
            $this->createNewCreditCardCommandPaymentWithNetbilling($data),
            $this->createNetbillingChargeSettings($data),
            $this->createCommandRebill($data)
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return PerformNetbillingExistingCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     * @throws InvalidPayloadException
     * @throws InvalidPaymentInformationException
     * @throws MissingInitialDaysException
     */
    protected function createPerformNetbillingExistingCreditCardSaleCommandWithRebill(
        array $data = null
    ): PerformNetbillingExistingCreditCardSaleCommand {
        return new PerformNetbillingExistingCreditCardSaleCommand(
            isset($data) && array_key_exists('siteId', $data) ? $data['siteId'] : $this->faker->uuid,
            isset($data) && array_key_exists('amount', $data) ? $data['amount'] : $this->faker->randomFloat(2, 1, 100),
            isset($data) && array_key_exists('currency', $data) ? $data['currency'] : 'USD',
            $this->createExistingCreditCardCommandPaymentWithNetbilling($data),
            $this->createNetbillingChargeSettings($data),
            $this->createCommandRebill($data),
            $this->createBillerLogin($data)
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return PerformNetbillingNewCreditCardSaleCommand
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     */
    protected function createPerformNetbillingSaleCommandSingleCharge(
        array $data = null
    ): PerformNetbillingNewCreditCardSaleCommand {
        return new PerformNetbillingNewCreditCardSaleCommand(
            $data['siteId'] ?? $this->faker->uuid,
            $data['amount'] ?? $this->faker->randomFloat(2, 1, 100),
            $data['currency'] ?? 'USD',
            $this->createNewCreditCardCommandPaymentWithNetbilling($data),
            $this->createNetbillingBillerFields($data),
            null
        );
    }

    /**
     * @param array|null $data Override Data
     * @return Payment
     * @throws Exception
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createExistingCreditCardCommandPaymentWithNetbilling(array $data = null): Payment
    {
        return new Payment(
            $data['type'] ?? 'cc',
            $this->createExistingCreditCardCommandInformation()
        );
    }

    /**
     * @param array|null   $data       Override Data
     * @param boolean|null $withMember with/without member
     * @return Payment
     * @throws Exception
     * @throws MissingCreditCardInformationException
     * @throws \Exception
     */
    protected function createNewCreditCardCommandPaymentWithNetbilling(array $data = null, $withMember = true): Payment
    {
        return new Payment(
            $data['type'] ?? 'cc',
            $this->createNewCreditCardCommandInformation($data, $withMember)
        );
    }

    /**
     * @param array|null   $data       Override Data
     * @param boolean|null $withMember with/without member
     *
     * @return ExistingCreditCardInformation
     * @throws MissingCreditCardInformationException
     * @throws LoggerException
     */
    protected function createExistingCreditCardCommandInformation(array $data = null, $withMember = false): ExistingCreditCardInformation
    {
        return new ExistingCreditCardInformation(
            $data['cardHash'] ?? $_ENV['NETBILLING_CARD_HASH']
        );
    }

    /**
     * @param array|null   $data       Override Data
     * @param boolean|null $withMember with/without member
     *
     * @return NewCreditCardInformation
     * @throws MissingCreditCardInformationException
     * @throws LoggerException
     * @throws InvalidCreditCardExpirationDateException
     */
    protected function createNewCreditCardCommandInformation(array $data = null, $withMember = true): NewCreditCardInformation
    {
        $memberData = null;
        if ($withMember || isset($data['member'])) {
            $memberData = $this->createNetbillingMember($data);
        }

        return new NewCreditCardInformation(
            $data['number'] ?? $this->faker->creditCardNumber('Visa'),
            $data['expirationMonth'] ?? (string) $this->faker->numberBetween(1, 12),
            $data['expirationYear'] ?? $this->faker->numberBetween(2025, 2030),
            $data['cvv'] ?? (string) $this->faker->numberBetween(100, 999),
            $memberData
        );
    }

    /**
     * @param array|null $data payload
     *
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    protected function createNetbillingPendingTransactionWithSingleCharge(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformNetbillingSaleCommandSingleCharge($data);

        return ChargeTransaction::createSingleChargeOnNetbilling(
            $command->siteId(),
            $command->amount(),
            NetbillingBillerSettings::NETBILLING,
            $command->currency(),
            $command->payment(),
            $command->billerFields()
        );
    }

    /**
     * @param array|null $data Override Data
     * @return Member
     */
    protected function createNetbillingMember(array $data = null): Member
    {
        return new Member(
            $data['ownerFirstName'] ?? $this->faker->name,
            $data['ownerLastName'] ?? $this->faker->lastName,
            $data['userName'] ?? $this->faker->userName,
            $data['email'] ?? $this->faker->email,
            $data['ownerPhoneNumber'] ?? $this->faker->phoneNumber,
            $data['ownerAddress'] ?? $this->faker->address,
            $data['ownerZip'] ?? $this->faker->postcode,
            $data['ownerCity'] ?? $this->faker->city,
            $data['ownerState'] ?? 'state',
            $data['ownerCountry'] ?? 'country',
            $data['password'] ?? $this->faker->password
        );
    }

    /**
     * @param array|null $data Override Data
     *
     * @return NetbillingChargeSettings
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     */
    protected function createNetbillingBillerFields(array $data = null): NetbillingChargeSettings
    {
        return NetbillingChargeSettings::create(
            $data['siteTag'] ?? $_ENV['NETBILLING_SITE_TAG_2'],
            $data['accountId'] ?? $_ENV['NETBILLING_ACCOUNT_ID_2'],
            $data['merchantPassword'] ?? $_ENV['NETBILLING_MERCHANT_PASSWORD'],
            $data['initialDays'] ?? 2,
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['browser'] ?? "browser",
            $data['host'] ?? "yuluserpool3.nat.as55222.com",
            $data['description'] ?? 'test-bundle',
            $data['binRouting'] ?? 'INTC/12345',
            $data['billerMemberId'] ?? '123456',
            $data['disableFraudChecks'] ?? false
            );
    }

    /**
     * @param array|null $data
     * @return NetbillingRebillUpdateSettings
     */
    protected function createNetbillingRebillUpdateBillerFields(array $data = null): NetbillingRebillUpdateSettings
    {
        return NetbillingRebillUpdateSettings::create(
            $data['siteTag'] ?? $_ENV['NETBILLING_SITE_TAG_2'],
            $data['accountId'] ?? $_ENV['NETBILLING_ACCOUNT_ID_2'],
            $data['billerMemberId'] ?? (string) $this->faker->numberBetween(1, 100),
            $data['merchantPassword'] ?? $this->faker->password,
            $data['initialDays'] ?? 2,
            $data['binRouting'] ?? 'INTC/12345',
            $data['ipAddress'] ?? $this->faker->ipv4,
            $data['browser'] ?? "browser",
            $data['host'] ?? "yuluserpool3.nat.as55222.com",
            $data['description'] ?? 'test-bundle'
        );
    }

    /**
     * @param array|null $data Override data
     *
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws LoggerException
     */
    protected function createNetbillingPendingTransactionWithRebillForNewCreditCard(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill($data);

        return ChargeTransaction::createWithRebillOnNetbilling(
            $command->siteId(),
            $command->amount(),
            NetbillingBillerSettings::NETBILLING,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill()
        );
    }

    /**
     * @param array|null $data Override
     *
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws InvalidPaymentInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    protected function createPendingTransactionWithRebillForExistingCreditCard(array $data = null): ChargeTransaction
    {
        $command = $this->createPerformNetbillingExistingCreditCardSaleCommandWithRebill($data);

        return ChargeTransaction::createWithRebillOnNetbilling(
            $command->siteId(),
            $command->amount(),
            NetbillingBillerSettings::NETBILLING,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill(),
            $command->billerLoginInfo()
        );
    }

    /**
     * @param array $data Override Data
     * @return PerformNetbillingCancelRebillCommand
     */
    protected function createPerformNetbillingCancelRebillCommand(
        array $data = []
    ): PerformNetbillingCancelRebillCommand {

        return new PerformNetbillingCancelRebillCommand(
            $data['transactionId'] ?? $this->faker->uuid,
            $data['siteTag'] ?? $_ENV['NETBILLING_SITE_TAG_2'],
            $data['accountId'] ?? $_ENV['NETBILLING_ACCOUNT_ID_2'],
            $data['merchantPassword'] ?? $this->faker->password,
        );
    }

    /**
     * @return RebillUpdateTransaction
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
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     * @throws \Exception
     */
    protected function createCancelRebillNetbillingTransaction(): RebillUpdateTransaction
    {
        $previousTransaction = $this->createNetbillingPendingTransactionWithSingleCharge();

        return RebillUpdateTransaction::createNetbillingCancelRebillTransaction(
            $previousTransaction,
            NetbillingBillerSettings::NETBILLING,
            $this->createNetbillingRebillUpdateBillerFields()
        );
    }

    /**
     * @param array|null $data overrides
     *
     * @return RebillUpdateTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createUpdateRebillNewCardNetbillingTransaction(array $data = null): RebillUpdateTransaction
    {
        $previousTransaction = $this->createNetbillingPendingTransactionWithSingleCharge();

        $paymentInformation = CreditCardInformation::create(
            $data['cvv2Check'] ?? true,
            $this->createCreditCardNumber($data),
            $this->createCreditCardOwner($data),
            $this->createCreditCardBillingAddress($data),
            $data['cvv'] ?? (string) $this->faker->numberBetween(100, 999),
            $data['expirationMonth'] ?? $this->faker->numberBetween(1, 12),
            $data['expirationYear'] ?? $this->faker->numberBetween(2025, 2030)
        );
        $chargeInformation  = ChargeInformation::createWithRebill(
            $this->createCurrency($data),
            $this->createAmount($data),
            $this->createRebill($data)
        );
        return RebillUpdateTransaction::createNetbillingUpdateRebillTransaction(
            $previousTransaction,
            NetbillingBillerSettings::NETBILLING,
            $this->createNetbillingRebillUpdateBillerFields(),
            $paymentInformation,
            $chargeInformation,
            'cc'
        );
    }

    /**
     * @param array|null $data
     *
     * @return RebillUpdateTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     */
    protected function createUpdateRebillExistingCardNetbillingTransaction(array $data = null): RebillUpdateTransaction
    {
        $previousTransaction = $this->createNetbillingPendingTransactionWithSingleCharge();

        $paymentInformation = NetbillingPaymentTemplateInformation::create(
            NetbillingCardHash::create($_ENV['NETBILLING_CARD_HASH'])
        );
        $chargeInformation  = ChargeInformation::createWithRebill(
            $this->createCurrency($data),
            $this->createAmount($data),
            $this->createRebill($data)
        );
        return RebillUpdateTransaction::createNetbillingUpdateRebillTransaction(
            $previousTransaction,
            NetbillingBillerSettings::NETBILLING,
            $this->createNetbillingRebillUpdateBillerFields(),
            $paymentInformation,
            $chargeInformation,
            'cc'
        );
    }

    /**
     * @param array $attributePathsToExclude Attribute Path To Exclude.
     *
     * @return array
     */
    protected function createDeclinedNetbillingBillerResponse(array $attributePathsToExclude = []): array
    {
        $data = [
            'code'     => 222,
            'reason'   => 'Just for test',
            'request'  => [
                'transactionId'  => $this->faker->uuid,
                'initialDays'    => '2',
                'siteTag'        => $_ENV['NETBILLING_SITE_TAG'],
                'accountId'      => $_ENV['NETBILLING_ACCOUNT_ID'],
                'ipAddress'      => '118.203.229.19',
                'browser'        => 'Mozilla\\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\\/537.36 (KHTML, like Gecko) Chrome\\\/77.0.3865.120 Safari\\\/537.36',
                'host'           => 'yuluserpool3.nat.as55222.com',
                'description'    => null,
                'routingCode'    => '',
                'memberUsername' => 'akeem.bode',
                'memberPassword' => '',
                'memberId'       => '',
                'amount'         => '27.8',
                'cardNumber'     => '*******',
                'cardExpire'     => '0130',
                'cardCvv2'       => '*******',
                'payType'        => 'cc',
                'firstName'      => 'Jonas Labadie DVM',
                'lastName'       => 'Bergnaum',
                'email'          => 'lindsay.boehm@hotmail.com',
                'address'        => '510 Kerluke Throughway\nEast Bertramborough, CT 35153-7948',
                'city'           => 'North Cathrineview',
                'state'          => 'QC',
                'zipCode'        => '99131',
                'phone'          => '+1.984.700.6752',
                'country'        => 'CA'
            ],
            'response' => [
                'avs_code'        => 'X',
                'cvv2_code'       => 'M',
                'status_code'     => '1',
                'processor'       => 'TEST',
                'settle_amount'   => '27.80',
                'settle_currency' => 'USD',
                'trans_id'        => '113999916308',
                'member_id'       => '113999670593',
                'auth_msg'        => 'Just for test',
                'recurring_id'    => '113999654212',
                'auth_date'       => '2020-07-06 13:20:51'
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
     * @param array $data
     *
     * @return array
     */
    public function getNetbillingMemberInfo($data = []): array
    {
        $firstName      = $this->faker->firstName;
        $lastName       = $this->faker->lastName;
        $email          = $firstName . '.' . $lastName . '@test.mindgeek.com';

        return [
            "firstName" => $data['firstName'] ?? $firstName,
            "lastName"  => $data['lastName'] ?? $lastName,
            "userName"  => $data['userName'] ?? $this->faker->userName,
            "email"     => $data['email'] ?? $email,
            "phone"     => $data['phone'] ?? $this->faker->phoneNumber,
            "address"   => $data['address'] ?? "7777 Decarie Blvd",
            "zipCode"   => $data['zipCode'] ?? "H4P2H2",
            "city"      => $data['city'] ?? "Montreal",
            "state"     => $data['state'] ?? "QC",
            "country"   => $data['country'] ?? "CA"
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getNetbillingPaymentInfo($data = []): array
    {
        return [
            "method"      => $data['method'] ?? "cc",
            "information" => [
                "number"          => $data['number'] ?? $_ENV['NETBILLING_CARD_NUMBER'],
                "expirationMonth" => $data['expirationMonth'] ?? $_ENV['NETBILLING_CARD_EXPIRE_MONTH'],
                "expirationYear"  => $data['expirationYear'] ?? $_ENV['NETBILLING_CARD_EXPIRE_YEAR'],
                "cvv"             => $data['cvv'] ?? $_ENV['NETBILLING_CARD_CVV2'],
                "member"          => $this->getNetbillingMemberInfo($data),
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getNetbillingBillerFields($data = []): array
    {
        return [
            "siteTag"            => $data['siteTag'] ?? $this->getSiteTag(),
            "accountId"          => $data['accountId'] ?? $this->getNetbillingAccountId(),
            'merchantPassword'   => $data['merchantPassword'] ?? $this->getControlKeyword(),
            "ipAddress"          => $data['ipAddress'] ?? $this->faker->ipv4,
            "initialDays"        => $data['initialDays'] ?? 2,
            "browser"            => $data['browser'] ?? "Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/77.0.3865.120 Safari\/537.36",
            "host"               => $data['host'] ?? "yuluserpool3.nat.as55222.com",
            "binRouting"         => $data['binRouting'] ?? $this->getNetbillingBinRouting(),
            "billerMemberId"     => $data['billerMemberId'] ?? null,
            "disableFraudChecks" => $data['disableFraudChecks'] ?? false
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function getNetbillingRebillInfo($data = []): array
    {
        return [
            'amount'    => $data['amount'] ?? $this->faker->randomFloat(2, 1, 15),
            'start'     => $data['start'] ?? 10,
            'frequency' => $data['frequency'] ?? 30
        ];
    }

    public function getSiteTag(): string
    {
        return $_ENV['NETBILLING_SITE_TAG'];
    }

    public function getNetbillingAccountId(): string
    {
        return $_ENV['NETBILLING_ACCOUNT_ID'];
    }

    public function getControlKeyword(): string
    {
        return $_ENV['NETBILLING_MERCHANT_PASSWORD'];
    }

    public function getNetbillingBinRouting(): string
    {
        return "INT/PX#100XTxEP";
    }
}
