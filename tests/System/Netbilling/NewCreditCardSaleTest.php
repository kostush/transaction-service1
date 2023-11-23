<?php
declare(strict_types=1);

namespace Tests\System\Netbilling;

use Symfony\Component\HttpFoundation\Response;
use Tests\CreateTransactionDataForNetbilling;
use Tests\SystemTestCase;

class NewCreditCardSaleTest extends SystemTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @var string
     */
    private $uri = '/api/v1/sale/newCard/netbilling/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var array
     */
    protected $fullPayload;

    /**
     * @var array
     */
    protected $minPayload;

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
            "siteId"       => "8e34c94e-135f-4acb-9141-58b3a6e56c74",
            "amount"       => $this->faker->randomFloat(2, 1, 15),
            "currency"     => "USD",
            "payment"      => $this->getNetbillingPaymentInfo(),
            "billerFields" => $this->getNetbillingBillerFields()
        ];

        $this->minPayload = [
            "siteId"       => "c465025d-5707-43df-93ed-ebbb0bbedcb0",
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "number"          => $this->faker->creditCardNumber('Visa'),
                    "expirationMonth" => $this->faker->numberBetween(1, 12),
                    "expirationYear"  => 2030,
                    "cvv"             => (string) $this->faker->numberBetween(100, 999)
                ],
            ],
            "billerFields" => [
                "siteTag"          => $_ENV['NETBILLING_SITE_TAG'],
                "accountId"        => $_ENV['NETBILLING_ACCOUNT_ID'],
                'merchantPassword' => $_ENV['NETBILLING_MERCHANT_PASSWORD'],
                "initialDays"      => 2
            ],
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_created_for_successful_sale_without_rebill(): array
    {
        $response = $this->json('POST', $this->uri, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_without_rebill
     *
     * @param array $responseContent The response array
     *
     * @return void
     */
    public function it_should_return_a_transaction_id_for_successful_sale(array $responseContent) : void
    {
        $this->assertArrayHasKey('transactionId', $responseContent);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_without_rebill
     *
     * @param array $responseContent The response array
     *
     * @return void
     */
    public function it_should_return_a_status_id_for_successful_sale(array $responseContent) : void
    {
        $this->assertArrayHasKey('status', $responseContent);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_without_rebill
     *
     * @param array $responseContent The response array
     *
     * @return void
     */
    public function status_should_be_approved_when_transaction_is_accepted_by_netbilling(array $responseContent) : void
    {
        $this->assertEquals('approved', $responseContent['status']);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_created_for_successful_sale_with_rebill() : void
    {
        $payload           = $this->fullPayload;
        $payload['rebill'] = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => $this->faker->numberBetween(1, 100),
            "start"     => $this->faker->numberBetween(1, 100)
        ];

        $response = $this->json('POST', $this->uri, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @return string
     */
    public function it_should_return_decline_sale(): string
    {
        // force a declined transaction
        $this->fullPayload['amount'] = 91;

        $response = $this->json('POST', $this->uri, $this->fullPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return $response->response->getContent();
    }

    /**
     * @test
     * @depends it_should_return_decline_sale
     *
     * @param string $responseContent The array key json
     *
     * @return void
     */
    public function decline_sale_response_should_contain_an_code(string $responseContent) : void
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('code', $responseContent);
    }

    /**
     * @test
     * @depends it_should_return_decline_sale
     *
     * @param string $responseContent The array key json
     *
     * @return void
     */
    public function decline_sale_response_should_contain_a_reason(string $responseContent) : void
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('reason', $responseContent);
    }

    /**
     * @test
     * @return string
     */
    public function it_should_return_aborted_sale() : string
    {
        $payload = $this->fullPayload;
        unset($payload['payment']['information']['member']['userName']);
        $payload['payment']['information']['number'] = $_ENV['NETBILLING_CARD_NUMBER_2'];

        $payload['billerFields']['siteTag']   = $_ENV['NETBILLING_SITE_TAG_2'];
        $payload['billerFields']['accountId'] = $_ENV['NETBILLING_ACCOUNT_ID_2'];
        $response                             = $this->json('POST', $this->uri, $payload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        return $response->response->getContent();
    }

    /**
     * @test
     * @depends it_should_return_aborted_sale
     *
     * @param string $responseContent The array key json
     *
     * @return void
     */
    public function aborted_sale_response_should_contain_an_code(string $responseContent) : void
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('code', $responseContent);
    }

    /**
     * @test
     * @depends it_should_return_aborted_sale
     *
     * @param string $responseContent The array key json
     *
     * @return void
     */
    public function aborted_sale_response_should_contain_a_reason(string $responseContent) : void
    {
        $responseContent = json_decode($responseContent, true);
        $this->assertArrayHasKey('reason', $responseContent);
    }

    /**
     * @test
     * @return void
     */
    public function sale_without_should_return_error_without_required_member_info() : void
    {
        $response = $this->json('POST', $this->uri, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @return void
     */
    public function sale_without_should_return_error_without_required_payment_info() : void
    {
        unset($this->minPayload['payment']);
        $response = $this->json('POST', $this->uri, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @return void
     */
    public function sale_without_should_return_error_without_required_biller_info() : void
    {
        unset($this->minPayload['billerFields']);
        $response = $this->json('POST', $this->uri, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @return void
     */
    public function sale_without_should_return_error_with_invalid_payment_method() : void
    {
        $memberInfo                                           = [
            "firstName" => $this->faker->name,
            "lastName"  => $this->faker->lastName,
            "email"     => $this->faker->email,
            "phone"     => $this->faker->phoneNumber,
            "address"   => $this->faker->address,
            "zipCode"   => $this->faker->postcode,
            "city"      => $this->faker->city,
            "state"     => "CA",
            "country"   => "CA"
        ];
        $this->minPayload['payment']['information']['member'] = $memberInfo;
        $this->minPayload['payment']['method']                = '';
        $response                                             = $this->json('POST', $this->uri, $this->minPayload);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_created_two_successful_sale_with_rebill_with_disable_fraud_checks_true_for_cross_sale() : void
    {
        $rebill = [
            "amount"    => $this->faker->randomFloat(2, 1, 100),
            "frequency" => $this->faker->numberBetween(1, 100),
            "start"     => $this->faker->numberBetween(1, 100)
        ];

        $mainPayload           = $this->fullPayload;
        $mainPayload['rebill'] = $rebill;

        $mainTransactionResponse = $this->json('POST', $this->uri, $mainPayload);
        $mainTransactionResponse->assertResponseStatus(Response::HTTP_CREATED);

        $mainTransactionResponseContent = json_decode($mainTransactionResponse->response->getContent(), true);
        $this->assertArrayHasKey('transactionId', $mainTransactionResponseContent);
        $this->assertArrayHasKey('status', $mainTransactionResponseContent);
        $this->assertEquals('approved', $mainTransactionResponseContent['status']);

        $crossSalePayload                                       = $this->fullPayload;
        $crossSalePayload['rebill']                             = $rebill;
        $crossSalePayload['billerFields']['disableFraudChecks'] = true;

        $crossSaleTransactionResponse = $this->json('POST', $this->uri, $crossSalePayload);
        $crossSaleTransactionResponse->assertResponseStatus(Response::HTTP_CREATED);

        $crossSaleTransactionResponseContent = json_decode($crossSaleTransactionResponse->response->getContent(), true);
        $this->assertArrayHasKey('transactionId', $crossSaleTransactionResponseContent);
        $this->assertArrayHasKey('status', $crossSaleTransactionResponseContent);
        $this->assertEquals('approved', $crossSaleTransactionResponseContent['status']);
    }

    /**
     * @test
     * @return array
     */
    public function decline_transaction_should_contain_mapping_criteria_and_error_classification() : array
    {
        // force a declined transaction
        $this->fullPayload['amount'] = 91;

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
}
