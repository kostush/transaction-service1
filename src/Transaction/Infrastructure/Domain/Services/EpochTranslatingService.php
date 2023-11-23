<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Epoch\Application\Services\NewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\EpochBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Services\EpochPostbackAdapter as EpochPostbackAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\EpochNewSaleAdapter as EpochNewSaleAdapterInterface;
use ProBillerNG\Transaction\Domain\Services\EpochService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochPostbackBillerResponse;

class EpochTranslatingService implements EpochService
{
    /** @var EpochPostbackAdapterInterface */
    protected $postbackAdapter;

    /** @var EpochNewSaleAdapterInterface */
    protected $newSaleAdapter;

    /**
     * EpochTranslatingService constructor.
     * @param EpochPostbackAdapterInterface $postbackAdapter
     * @param EpochNewSaleAdapterInterface  $newSaleAdapter
     */
    public function __construct(
        EpochPostbackAdapterInterface $postbackAdapter,
        EpochNewSaleAdapterInterface $newSaleAdapter
    ) {
        $this->postbackAdapter = $postbackAdapter;
        $this->newSaleAdapter  = $newSaleAdapter;
    }

    /**
     * @param array  $payload         The payload coming from epoch
     * @param string $transactionType Transaction Type [join, etc]
     * @param string $digestKey       The digest key used by the epoch library to validate digest
     * @return EpochPostbackBillerResponse
     * @throws \Exception
     */
    public function translatePostback(
        array $payload,
        string $transactionType,
        string $digestKey
    ): EpochPostbackBillerResponse {
        return $this->postbackAdapter->getTranslatedPostback($payload, $transactionType, $digestKey);
    }

    /**
     * @param array  $transactions The Transactions Array
     * @param array  $taxArray     The Tax array
     * @param string $sessionId    The Session Id
     * @param Member $member       Member
     * @return EpochNewSaleBillerResponse
     * @throws \ProBillerNG\Epoch\Domain\Model\Exception\MissingNewSaleInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function chargeNewSale(
        array $transactions,
        array $taxArray,
        string $sessionId,
        Member $member
    ): EpochNewSaleBillerResponse {
        $purchases = [];
        /** @var ChargeTransaction $mainTransaction */
        $mainTransaction = $transactions[0];
        /** @var EpochBillerChargeSettings $chargeSettings */
        $chargeSettings = $mainTransaction->billerChargeSettings();

        foreach ($transactions as $key => $transaction) {
            $purchases[] = $this->buildPuchase($transaction, $chargeSettings, $taxArray[$key] ?? [], $sessionId);
        }

        // add user and password
        if ($member !== null && $member->userName() !== null) {
            $purchases[0]['username'] = $member->userName();
        }

        if ($member !== null && $member->password() !== null) {
            $purchases[0]['password'] = $member->password();
        }

        //add member id in case of a sec rev
        $memberId = null;
        if ($member !== null && $member->memberId() !== null) {
            $memberId = $member->memberId();
        }

        $command = new NewSaleCommand(
            (string) $mainTransaction->transactionId(),
            $chargeSettings->clientId(),
            $chargeSettings->clientKey(),
            (string) $chargeSettings->invoiceId(),
            $chargeSettings->redirectUrl(),
            $this->buildCustomer($member),
            [$purchases[0]],
            array_slice($purchases, 1),
            $memberId,
            env('BILLER_EPOCH_TEST_MODE', false)
        );

        return $this->newSaleAdapter->newSale($command);
    }

    /**
     * @param ChargeTransaction         $transaction    Charge Transaction
     * @param EpochBillerChargeSettings $chargeSettings Epoch Biller Settings
     * @param array                     $tax            Tax info
     * @param string                    $sessionId      NG PGW Session Id
     * @return array
     */
    private function buildPuchase(
        ChargeTransaction $transaction,
        EpochBillerChargeSettings $chargeSettings,
        array $tax,
        string $sessionId
    ): array {
        $purchase = [
            'passthru'     => [
                'ngSessionId'     => $sessionId,
                'ngTransactionId' => (string) $transaction->transactionId(),
            ],
            'tax'          => $tax,
            'postback_url' => $chargeSettings->notificationUrl(),
            'site'         => $transaction->siteName(),
        ];

        if ($transaction->chargeInformation()->rebill() === null) {
            $purchase['billing'] = [
                'currency' => (string) $transaction->chargeInformation()->currency(),
                'initial'  => [
                    'amount' => (string) $transaction->chargeInformation()->amount()
                ]
            ];
        } else {
            $purchase['billing'] = [
                'currency'  => (string) $transaction->chargeInformation()->currency(),
                'initial'   => [
                    'amount'             => (string) $transaction->chargeInformation()->amount(),
                    'valid_until_period' => $transaction->chargeInformation()->rebill()->start(),
                    'valid_until_unit'   => 'DAY',
                ],
                'recurring' => [
                    [
                        'amount'    => (string) $transaction->chargeInformation()->rebill()->amount(),
                        'frequency' => $transaction->chargeInformation()->rebill()->frequency(),
                        'unit'      => 'DAY'
                    ]
                ]
            ];
        }

        return $purchase;
    }

    /**
     * @param Member|null $member Member
     * @return array
     */
    private function buildCustomer(?Member $member): array
    {
        $customer = [];

        if($member === null) {
            return $customer;
        }

        $name = trim($member->firstName() . ' ' . $member->lastName());

        if (!empty($name)) {
            $customer['name'] = $name;
        }

        if ($member->email() !== null) {
            $customer['email'] = $member->email();
        }

        if ($member->zipCode() !== null) {
            $customer['postal_code'] = $member->zipCode();
        }

        return $customer;
    }
}
