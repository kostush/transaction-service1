<?php

namespace Infrastructure\Domain\Services\Pumapay;

use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Pumapay\PumapayFirestoreSerializer;
use Tests\UnitTestCase;

class PumapayFirestoreSerializerTest extends UnitTestCase
{
    /**
     * @return array
     */
    public function getFirestoreTransaction(): array
    {
        return [
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

            'createdAt'                       => 'August 26, 2021 at 7:06:13 PM UTC',
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
            'updatedAt'                       => 'August 26, 2021 at 7:06:13 PM UTC',
            'version'                         => 1
        ];
    }

    /**
     * @test
     */
    public function it_should_return_pumapay_transaction_whith_rebill()
    {
        // Create transaction with rebill data
        $transaction = PumapayFirestoreSerializer::createTransaction($this->getFirestoreTransaction());
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals(BillerSettings::PUMAPAY, $transaction->billerName());
        $this->assertEquals(PaymentType::CRYPTO, $transaction->paymentType());
        $this->assertInstanceOf(Rebill::class, $transaction->chargeInformation()->rebill());
    }

    /**
     * @test
     */
    public function it_should_return_pumapay_transaction_whithout_rebill()
    {
        $data = $this->getFirestoreTransaction();

        // Removing rebill information
        unset($data['chargeInformation']['rebill']);

        // Create transaction without rebill data
        $transaction = PumapayFirestoreSerializer::createTransaction($data);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertNull( $transaction->chargeInformation()->rebill());
    }
}