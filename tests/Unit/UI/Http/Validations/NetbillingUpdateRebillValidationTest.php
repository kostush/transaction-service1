<?php
declare(strict_types=1);

namespace Tests\Unit\UI\Http\Validations;

use Illuminate\Http\Request;
use ProBillerNG\Transaction\UI\Http\Validations\EpochNewSaleValidation;
use ProBillerNG\Transaction\UI\Http\Validations\NetbillingUpdateRebillValidation;
use Tests\UnitTestCase;

class NetbillingUpdateRebillValidationTest extends UnitTestCase
{

    /**
     * @var array
     */
    private $updateRebillCreditCardPayload;

    /**
     * @var string
     */
    private $lastFour;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $firstName = $this->faker->firstName;
        $lastName  = $this->faker->lastName;
        $email     = $firstName . '.' . $lastName . '@test.mindgeek.com';

        $this->updateRebillCreditCardPayload = [
            'transactionId'    => 'd465025d-5707-43df-93ed-ebbb0bbedcb1',
            'siteTag'          => $_ENV['NETBILLING_SITE_TAG'],
            'accountId'        => $_ENV['NETBILLING_ACCOUNT_ID'],
            'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
            "binRouting"       => "INT/PX#100XTxEP",
            'amount'           => $this->faker->randomFloat(2, 1, 15),
            'currency'         => "USD",
            'updateRebill'     => [
                'amount'    => $this->faker->randomFloat(2, 1, 15),
                'start'     => 10,
                'frequency' => 365
            ],
            "payment"          => [
                "method"      => "cc",
                "information" => [
                    "number"          => $this->faker->creditCardNumber('Visa'),
                    "expirationMonth" => $this->faker->numberBetween(1, 12),
                    "expirationYear"  => 2030,
                    "cvv"             => (string) $this->faker->numberBetween(100, 999),
                    "member"          => [
                        "firstName" => $firstName,
                        "lastName"  => $lastName,
                        "userName"  => $this->faker->userName,
                        "email"     => $email,
                        "phone"     => $this->faker->phoneNumber,
                        "address"   => "7777 Decarie Blvd",
                        "zipCode"   => "H4P2H2",
                        "city"      => "Montreal",
                        "state"     => "QC",
                        "country"   => "CA"
                    ]
                ],
            ]
        ];
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_evaluate_as_true_when_the_request_data_is_valid_with_new_cc(): void
    {
        $request = new Request(
            $this->updateRebillCreditCardPayload
        );

        NetbillingUpdateRebillValidation::validate($request);

        $this->assertTrue(true);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_evaluate_as_true_when_the_request_data_is_valid_with_card_hash(): void
    {

        $updateRebill = $this->updateRebillCreditCardPayload;

        $existingPayment = [
            "method"      => "cc",
            "information" => [
                "cardHash" => $_ENV['NETBILLING_CARD_HASH'],
            ]
        ];

        unset($updateRebill['payment']);
        $updateRebill['payment'] = $existingPayment;

        $request = new Request(
            $this->updateRebillCreditCardPayload
        );

        NetbillingUpdateRebillValidation::validate($request);

        $this->assertTrue(true);
    }

}