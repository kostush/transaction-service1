<?php
declare(strict_types=1);

namespace Tests\System\Netbilling;

use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class ExistingCreditCardSaleTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $uri = '/api/v1/sale/existingCard/netbilling/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var array
     */
    protected $fullPayload;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        /** pre-check for netbilling card info loading from .env.testing file */
        $this->checkNetbillingTestCardInfoBeforeRunningTest();

        $this->fullPayload = [
            "siteId"       => "8051d60a-7fb0-4ef2-8e60-968eee79c104",
            "amount"       => $this->faker->randomFloat(2, 1, 15),
            "currency"     => "USD",
            "rebill"       =>  [
                "amount"    => $this->faker->randomFloat(2, 1, 100),
                "frequency" => $this->faker->numberBetween(1, 100),
                "start"     => $this->faker->numberBetween(1, 100)
            ],
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "cardHash" => $_ENV['NETBILLING_CARD_HASH']
                ],
            ],
            "billerFields" => [
                "siteTag"            => $_ENV['NETBILLING_SITE_TAG'],
                "accountId"          => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword'   => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                "ipAddress"          => $this->faker->ipv4,
                "initialDays"        => 2,
                "browser"            => "Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/77.0.3865.120 Safari\/537.36",
                "host"               => "yuluserpool3.nat.as55222.com",
                "binRouting"         => "INT/PX#100XTxEP"
            ],
            "member" => [
                "userName" => "GiselleF",
                "password" => "secretPassword"
            ]
        ];
    }

    /**
     * @test
     * @return array
     */
    public function decline_transaction_should_contain_mapping_criteria_and_error_classification() : array
    {
        // create initial transaction
        $initialTransaction = $this->createInitialTransaction();

        $transaction = $this->retrieveTransaction($initialTransaction['transactionId']);

        // force a declined transaction
        $this->fullPayload['amount'] = 91;
        $this->fullPayload['payment']['information']['cardHash'] = $transaction['card_hash'];

        $response = $this->json('POST', $this->uri, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_status(array $responseContent) : void
    {
        $this->assertArrayHasKey('status', $responseContent);
        $this->assertEquals('declined', $responseContent['status']);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_error_classification(array $responseContent) : void
    {
        $this->assertArrayHasKey('errorClassification', $responseContent);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_decline_mapping_criteria(array $responseContent) : void
    {
        $this->assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_code(array $responseContent) : void
    {
        $this->assertArrayHasKey('code', $responseContent);
    }

    /**
     * @test
     * @depends decline_transaction_should_contain_mapping_criteria_and_error_classification
     *
     * @param array $responseContent Response
     *
     * @return void
     */
    public function decline_sale_response_should_contain_a_reason(array $responseContent) : void
    {
        $this->assertArrayHasKey('reason', $responseContent);
    }

    /**
     * @return array
     */
    private function createInitialTransaction() : array
    {
        $initialPayload = $this->fullPayload;

        unset($initialPayload['rebill']);
        unset($initialPayload['member']);

        $initialPayload['payment'] = [
            "method"      => "cc",
            "information" => [
                "number"          => $_ENV['NETBILLING_CARD_NUMBER_2'],
                "expirationMonth" => $_ENV['NETBILLING_CARD_EXPIRE_MONTH'],
                "expirationYear"  => $_ENV['NETBILLING_CARD_EXPIRE_YEAR'],
                "cvv"             => $_ENV['NETBILLING_CARD_CVV2'],
                "member"          => [
                    "firstName" => 'Gisele',
                    "lastName"  => 'Framboise',
                    "userName"  => 'GiseleF',
                    "email"     => 'txnpnsmk001@test.mindgeek.com',
                    "phone"     => $this->faker->phoneNumber,
                    "address"   => "7777 Decarie Blvd",
                    "zipCode"   => "H4P2H2",
                    "city"      => "Montreal",
                    "state"     => "QC",
                    "country"   => "CA"
                ]
            ],
        ];

        // create initial transaction
        $response = $this->json(
            'POST',
            '/api/v1/sale/newCard/netbilling/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70',
            $initialPayload
        );

        $response->assertResponseStatus(Response::HTTP_CREATED);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @param string $transactionId
     * @return array
     */
    private function retrieveTransaction(string $transactionId) : array
    {
        // retrieve the transaction to get the card hash
        $response = $this->get(
            '/api/v1/transaction/' . $transactionId. '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70'
        );

        $response->assertResponseStatus(\Illuminate\Http\Response::HTTP_OK);

        return json_decode($response->response->getContent(), true);
    }
}
