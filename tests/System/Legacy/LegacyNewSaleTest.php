<?php
declare(strict_types=1);

namespace Tests\System\Legacy;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Collection\BillerInteractionCollection;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

/**
 * @group legacyService
 * Class LegacyNewSaleTest
 * @package Tests\System\Legacy
 */
class LegacyNewSaleTest extends SystemTestCase
{
    const CORRELATION_ID = 'X-CORRELATION-ID';

    /**
     * @var string
     */
    private $uri = '/api/v1/sale/biller/vendo/session/b0c56eaf-0ce8-4884-9bae-076c69a2b0af';

    /**
     * @var TransactionRepository
     */
    private $repository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(TransactionRepository::class);
    }

    /**
     * @return array
     */
    public static function payload(): array
    {
        return [
            'payment'      => [
                'type'        => 'cc',
                'method'      => 'visa',
                'information' => [
                    'member' => [
                        "firstName" => "Centrbill",
                        "lastName"  => "sdasdsdsd",
                        "userName"  => "asdasdadad",
                        "password"  => "123456test",
                        "email"     => "aa.ff@test.mindgeek.com",
                        "phone"     => "514 000-0000",
                        "address"   => "7777 Decarie",
                        "zipCode"   => "H1H1H1",
                        "city"      => "Montreal",
                        "state"     => "QC",
                        "country"   => "CA"
                    ]
                ]
            ],
            'charges' => [
                    [
                        'siteId'         => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
                        "amount"         => "14.97",
                        "currency"       => "USD",
                        "productId"      => 15,
                        "isMainPurchase" => true,
                        'rebill'         => [
                            'amount'    => "10",
                            'frequency' => 365,
                            'start'     => 30
                        ],
                        'tax'            => [
                            'initialAmount'    => [
                                'beforeTaxes' => 14.23,
                                'taxes'       => 0.74,
                                'afterTaxes'  => 14.97
                            ],
                            'rebillAmount'     => [
                                'beforeTaxes' => 9.5,
                                'taxes'       => 0.5,
                                'afterTaxes'  => 10
                            ],
                            'taxApplicationId' => '60bf5bcb-ac64-496c-acc5-9c7cf54a1869',
                            'taxName'          => 'Tax Name',
                            'taxRate'          => 0.05,
                            'taxType'          => 'vat'
                        ]
                    ]
                ],
            'billerFields' => [
                'legacyMemberId' => 101988,
                'returnUrl'      => 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt',
                'postbackUrl'    => 'http://postback-purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt'
            ]
        ];
    }

    /**
     * @test
     * @return void
     */
    public function legacy_should_return_created_for_successful_sale(): void
    {
        $response = $this->json(
            'POST',
            $this->uri,
            self::payload()
        );

        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @depends legacy_should_return_created_for_successful_sale
     * @return void
     */
    public function legacy_should_return_status_pending_for_successful_sale(): void
    {
        $response        = $this->json(
            'POST',
            $this->uri,
            self::payload()
        );
        $responseContent = json_decode($response->response->getContent(), true);
        $this->assertSame('pending', $responseContent['status']);
    }

    /**
     * @test
     * @return void
     */
    public function legacy_should_return_bad_request_for_missing_fields(): void
    {
        $payload = self::payload();

        $payload['charges'] = [];

        $response = $this->json(
            'POST',
            $this->uri,
            $payload
        );

        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array $payload Payload with wrong field
     * @dataProvider payloadWithWrongType
     * @test
     * @return void
     */
    public function legacy_should_validate_request_fields_returning_bad_request_for_wrong_type(array $payload): void
    {
        $response = $this->json(
            'POST',
            $this->uri,
            $payload
        );

        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function payloadWithWrongType():array
    {
        $invalidFirstNamePayload     = self::payload();
        $numericMainPurchasePayload  = self::payload();
        $invalidDisplayChargedAmount = self::payload();

        $invalidFirstNamePayload['payment']['information']['member']['firstName'] = 123;
        $numericMainPurchasePayload['charges'][0]['isMainPurchase']               = 1;
        $invalidDisplayChargedAmount['charges'][0]['tax']['displayChargedAmount'] = 1;

        return [
            'numeric_first_name'        => $invalidFirstNamePayload,
            'non_boolean_main_purchase' => $numericMainPurchasePayload,
            'non_boolean_display_tax'   => $invalidDisplayChargedAmount
        ];
    }

    /**
     * @test
     * @return void
     */
    public function legacy_should_create_a_transaction_without_member(): void
    {
        $payload = self::payload();

        $payload['payment']['information'] = [];

        $response = $this->json(
            'POST',
            $this->uri,
            $payload
        );

        $response->assertResponseStatus(Response::HTTP_CREATED);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function legacy_new_sale_should_take_correlation_id_from_header(): void
    {
        $correlationId = $this->faker->uuid;

        $response = $this->json(
            'POST',
            $this->uri,
            self::payload(),
            [self::CORRELATION_ID => $correlationId]
        );

        $responseContent = json_decode($response->response->getContent(), true);

        $this->assertSame($correlationId, $responseContent['correlationId']);
        $this->assertEquals($correlationId, Log::getCorrelationId());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function legacy_new_sale_should_have_correlation_id_on_header_response(): void
    {
        $correlationId = $this->faker->uuid;

        $response = $this->json(
            'POST',
            $this->uri,
            self::payload(),
            [self::CORRELATION_ID => $correlationId]
        );

        $this->assertEquals($correlationId, $response->response->headers->get('X-CORRELATION-ID'));
    }

    /**
     * @test
     * @return void
     */
    public function it_should_create_request_and_response_on_database(): void
    {
        $response = $this->json(
            'POST',
            $this->uri,
            self::payload()
        );

        $responseContent = json_decode($response->response->getContent(), true);

        $transactionId = $responseContent['transactionId'];

        $transaction = $this->repository->findById($transactionId);

        /** @var BillerInteractionCollection $billerInteractions */
        $billerInteractions = $transaction->billerInteractions();

        $arrayOfTypes = [];
        /** @var BillerInteraction $billerInteraction */
        foreach ($billerInteractions->toArray() as $billerInteraction) {
            $arrayOfTypes[$billerInteraction->type()] = null;
        }

        $this->assertArrayHasKey('response', $arrayOfTypes);
        $this->assertArrayHasKey('request', $arrayOfTypes);
    }
}
