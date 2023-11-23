<?php
declare(strict_types=1);

namespace Tests\System\Netbilling;

use Tests\CreateTransactionDataForNetbilling;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class NetbillingCancelRebillControllerTest extends SystemTestCase
{
    use CreateTransactionDataForNetbilling;
    /**
     * @var string
     */
    private $newCardSaleUri = '/api/v1/sale/newCard/netbilling/session/1ad9a902-3b16-4d14-a7e9-21cf93404e8e';

    /**
     * @var array
     */
    private $newCardSalePayload;

    /**
     * @var string
     */
    private $uri = '/api/v1/cancelRebill/netbilling';

    private $validRequestData;

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

        $this->newCardSalePayload = [
            "siteId"       => "8e34c94e-135f-4acb-9141-58b3a6e56c74",
            "amount"       => $this->faker->randomFloat(2, 1, 15),
            "currency"     => "USD",
            'rebill'     => $this->getNetbillingRebillInfo(),
            "payment"      => $this->getNetbillingPaymentInfo(),
            "billerFields" => $this->getNetbillingBillerFields()
        ];

        $this->validRequestData = [
            'siteTag'            => $this->getSiteTag(),
            'accountId'          => $this->getNetbillingAccountId(),
            'merchantPassword'   => $this->getControlKeyword()
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_created_for_successful_sale_cancel(): array
    {
        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardSalePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_cancel
     * @param array $response
     * @return array
     */
    public function it_should_create_a_transaction_when_suspend_is_accepted_by_netbilling(array $response): array
    {
        $this->validRequestData['transactionId'] = $response['transactionId'];

        $response = $this->json('POST', $this->uri, $this->validRequestData);
        $response->assertResponseStatus(Response::HTTP_CREATED);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_suspend_is_accepted_by_netbilling
     * @param array $response Response
     * @return void
     */
    public function status_should_be_approved_when_suspend_is_accepted_by_netbilling(array $response)
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_cancel
     * @param $response
     * @return array
     */
    public function it_should_create_a_transaction_when_merchantPassword_is_invalid($response): array
    {
        $this->validRequestData['transactionId'] = $response['transactionId'];
        $this->validRequestData['merchantPassword'] = 'invalidMerchantPassword';

        $response = $this->json('POST', $this->uri, $this->validRequestData);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_merchantPassword_is_invalid
     * @param array $response Response
     * @return void
     */
    public function status_should_be_declined_when_suspend_is_declined_by_netbilling(array $response)
    {
        $this->assertEquals('declined', $response['status']);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_merchantPassword_is_invalid
     * @param array $response Response
     * @return void
     */
    public function code_should_be_9999_when_suspend_is_declined_by_netbilling(array $response)
    {
        $this->assertEquals('9999', $response['code']);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_merchantPassword_is_invalid
     * @param array $response Response
     * @return void
     */
    public function it_should_return_valid_reason_when_invalid_merchantPassword_is_provided(array $response)
    {
        $this->assertEquals(
            '400 Invalid site access keyword: Attempt logged in security audit log',
            $response['reason']
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_a_bad_request_when_invalid_payload_is_provided(): void
    {
        $this->validRequestData['transactionId'] = '';
        $response = $this->json('POST', $this->uri, $this->validRequestData);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_cancel
     * @param $response
     * @return array
     */
    public function it_should_create_a_transaction_when_siteTag_is_invalid($response): array
    {
        $this->validRequestData['transactionId'] = $response['transactionId'];
        $this->validRequestData['siteTag'] = 'invalidSiteTag';

        $response = $this->json('POST', $this->uri, $this->validRequestData);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_siteTag_is_invalid
     *
     * @param array $response Response
     *
     * @return void
     */
    public function it_should_return_valid_reason_when_invalid_siteTag_is_provided(array $response)
    {
        $this->assertEquals(
            '400 Member not found',
            $response['reason']
        );
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale_cancel
     * @param $response
     * @return array
     */
    public function it_should_create_a_transaction_when_accountId_is_invalid(array $response): array
    {
        $this->validRequestData['transactionId'] = $response['transactionId'];
        $this->validRequestData['accountId'] = 'invalidAccountId';

        $response = $this->json('POST', $this->uri, $this->validRequestData);
        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);

        /** We have added this sleep time as we are sending too many request to Netbilling during the system test
         * Some tests are getting failed simultaneously because of that.
         */
        sleep(2);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_when_accountId_is_invalid
     * @param array $response Response
     * @return void
     */
    public function it_should_return_valid_reason_when_invalid_accountId_is_providedarray(array $response)
    {
        $this->assertEquals(
            '400 C_ACCOUNT parameter ID must be numeric',
            $response['reason']
        );
    }
}