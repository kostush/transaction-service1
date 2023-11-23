<?php
declare(strict_types=1);

namespace Tests\System\Netbilling;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use Tests\CreateTransactionDataForNetbilling;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class PerformNetbillingUpdateRebillTest extends SystemTestCase
{
    use CreateTransactionDataForNetbilling;
    /**
     * @var string
     */
    private $newCardSaleUri = '/api/v1/sale/newCard/netbilling/session/1ad9a902-3b16-4d14-a7e9-21cf93404e8e';

    /**
     * @var string
     */
    private $updateRebillUri = '/api/v1/updateRebill/netbilling';

    /**
     * @var array
     */
    private $newCardSalePayload;

    /**
     * @var array
     */
    private $updateRebillPaymentTemplatePayload;

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

        /** pre-check for netbilling card info loading from .env.testing file */
        $this->checkNetbillingTestCardInfoBeforeRunningTest();

        $this->lastFour = "6585";

        $this->updateRebillPaymentTemplatePayload = [
            'siteTag'          => $this->getSiteTag(),
            'accountId'        => $this->getNetbillingAccountId(),
            'merchantPassword' => $this->getControlKeyword(),
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
                    "cardHash" => $_ENV['NETBILLING_CARD_HASH'],
                ],
            ],
        ];

        $this->updateRebillCreditCardPayload = [
            'siteTag'          => $this->getSiteTag(),
            'accountId'        => $this->getNetbillingAccountId(),
            'merchantPassword' => $this->getControlKeyword(),
            "binRouting"       => $this->getNetbillingBinRouting(),
            'amount'           => $this->faker->randomFloat(2, 1, 15),
            'currency'         => "USD",
            'updateRebill'     => $this->getNetbillingRebillInfo(),
            "payment"          => $this->getNetbillingPaymentInfo()
        ];

        $this->newCardSalePayload = [
            "siteId"       => "c465025d-5707-43df-93ed-ebbb0bbedcb2",
            "amount"       => $this->faker->randomFloat(2, 1, 15),
            "currency"     => "USD",
            "payment"      => $this->getNetbillingPaymentInfo(),
            "billerFields" => $this->getNetbillingBillerFields()
        ];
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_created_for_successful_sale(): array
    {
        $response = $this->json('POST', $this->newCardSaleUri, $this->newCardSalePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        sleep(2);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response
     * @return array
     */
    public function it_should_return_created_for_successful_rebill_update_with_payment_template(array $response): array
    {
        $this->updateRebillPaymentTemplatePayload["transactionId"] = $response['transactionId'];
        $response                                                  = $this->json('POST', $this->updateRebillUri, $this->updateRebillPaymentTemplatePayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        sleep(2);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_rebill_update_with_payment_template
     * @param array $response
     * @return void
     */
    public function it_should_have_status_in_rebill_update_response(array $response): void
    {
        $this->assertArrayHasKey('status', $response);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_rebill_update_with_payment_template
     * @param array $response
     * @return void
     */
    public function it_should_return_status_approved_for_successful_rebill_update(array $response): void
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response successful sale
     * @return array
     */
    public function it_should_return_created_for_rebill_update_with_new_credit_card(array $response)
    {
        $this->updateRebillCreditCardPayload['transactionId'] = $response['transactionId'];

        $response = $this->json('POST', $this->updateRebillUri, $this->updateRebillCreditCardPayload);
        $response->assertResponseStatus(Response::HTTP_CREATED);
        sleep(2);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_rebill_update_with_new_credit_card
     * @param array $response rebill update response
     * @return void
     */
    public function it_should_return_status_approved_for_successful_rebill_update_with_new_cc(array $response): void
    {
        $this->assertEquals('approved', $response['status']);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response rebill update response
     * @return array
     */
    public function it_should_decline_transaction_with_invalid_site_tag_for_existing_card(array $response): array
    {
        $this->updateRebillPaymentTemplatePayload['transactionId'] = $response['transactionId'];
        $this->updateRebillPaymentTemplatePayload['siteTag']       = 'invalid';

        $response = $this->json('POST', $this->updateRebillUri, $this->updateRebillPaymentTemplatePayload);
        $status   = json_decode($response->response->getContent(), true)['status'];
        $this->assertEquals('declined', $status);

        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function decline_sale_response_should_contain_decline_error_classification(array $responseContent): void
    {
        self::assertArrayHasKey('errorClassification', $responseContent);
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function decline_sale_response_should_contain_decline_mapping_criteria(array $responseContent): void
    {
        self::assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_group_decline(array $responseContent): void
    {
        self::assertArrayHasKey('groupDecline', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE,
            $responseContent['errorClassification']['groupDecline']
        );
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_errorType(array $responseContent): void
    {
        self::assertArrayHasKey('errorType', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_ERROR_TYPE,
            $responseContent['errorClassification']['errorType']
        );
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_groupMessage(array $responseContent): void
    {
        self::assertArrayHasKey('groupMessage', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_GROUP_MESSAGE,
            $responseContent['errorClassification']['groupMessage']
        );
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_default_recommendedAction(array $responseContent): void
    {
        self::assertArrayHasKey('recommendedAction', $responseContent['errorClassification']);
        self::assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION,
            $responseContent['errorClassification']['recommendedAction']
        );
    }

    /**
     * @test
     * @depends it_should_decline_transaction_with_invalid_site_tag_for_existing_card
     * @param array $responseContent Response
     * @return void
     */
    public function error_classification_should_contain_mappingCriteria(array $responseContent): void
    {
        self::assertArrayHasKey('mappingCriteria', $responseContent['errorClassification']);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response rebill update response
     * @return void
     */
    public function it_should_decline_transaction_with_invalid_site_tag_for_new_card(array $response): void
    {
        $this->updateRebillCreditCardPayload['transactionId'] = $response['transactionId'];
        $this->updateRebillCreditCardPayload['siteTag']       = 'invalid';

        $response = $this->json('POST', $this->updateRebillUri, $this->updateRebillCreditCardPayload);
        $status   = json_decode($response->response->getContent(), true)['status'];
        $this->assertEquals('declined', $status);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response rebill update response
     * @return void
     */
    public function it_should_decline_transaction_with_invalid_merchant_password_for_existing_card(array $response): void
    {
        $this->updateRebillPaymentTemplatePayload['transactionId']    = $response['transactionId'];
        $this->updateRebillPaymentTemplatePayload['merchantPassword'] = 'invalid';

        $response = $this->json('POST', $this->updateRebillUri, $this->updateRebillPaymentTemplatePayload);
        $status   = json_decode($response->response->getContent(), true)['status'];
        $this->assertEquals('declined', $status);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_sale
     * @param array $response rebill update response
     * @return void
     */
    public function it_should_decline_transaction_with_invalid_merchant_password_for_new_card(array $response): void
    {
        $this->updateRebillCreditCardPayload['transactionId'] = $response['transactionId'];
        $this->updateRebillCreditCardPayload['siteTag']       = 'invalid';

        $response = $this->json('POST', $this->updateRebillUri, $this->updateRebillCreditCardPayload);
        $status   = json_decode($response->response->getContent(), true)['status'];
        $this->assertEquals('declined', $status);
    }
}
