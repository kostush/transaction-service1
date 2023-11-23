<?php
declare(strict_types=1);

namespace Tests\System\Qysso;

use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\FirestoreSerializer;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

/**
 * Class QyssoNewSaleTest
 * @package Tests\System\Qysso
 * @group   qysso
 */
class QyssoNewSaleTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $uri = '/api/v1/sale/qysso';

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
     * @param string|null $personalHashKey Qysso personal hash key.
     * @return array
     */
    public static function payload(?string $personalHashKey = null): array
    {
        return [
            'siteId'       => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
            'siteName'     => 'www.realitykings.com',
            'clientIp'     => '0.0.0.0',
            'amount'       => 14.97,
            'currency'     => 'USD',
            'payment'      => [
                'type'        => 'banktransfer',
                'method'      => 'zelle',
                'information' => [
                    'member' => [
                        'memberId'  => '123',
                        'userName'  => 'username',
                        'password'  => 'password',
                        'firstName' => 'firstName',
                        'lastName'  => 'lastName',
                        'email'     => 'email@test.mindgeek.com',
                        'zipCode'   => 'zipCode',
                        'address'   => '7777 decarie',
                        'city'      => 'montreal',
                        'country'   => 'ca',
                        'phone'     => 'ca',
                    ]
                ]
            ],
            'rebill'       => [
                'amount'    => 10,
                'frequency' => 365,
                'start'     => 30
            ],
            'tax'          => [
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
            ],
            'billerFields' => [
                'companyNum'      => $_ENV['QYSSO_COMPANY_NUM'],
                'personalHashKey' => $personalHashKey ?? $_ENV['QYSSO_PERSONAL_HASH_KEY'],
                'redirectUrl'     => 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/[jwt]',
                'notificationUrl' => 'https://postback-gateway.probiller.com/api/postbacks/[UUID]'
            ]
        ];
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_created_for_successful_sale_onecharge_with_minimal_member_payload(): void
    {
        $this->markTestSkipped("We are skipping this Qysso related test because Qysso have issue with their URL. Until we receive valid URL from their end we will skip Qysso tests");

        $payload = self::payload();
        unset($payload['member']);
        $payload['member'] = ['email' => $this->faker->email];
        unset($payload['rebill']);
        unset($payload['tax']['rebillAmount']);

        $this->json('POST', $this->uri, $payload);

        /**
         * In case of error on the request, we received the message direct on the test case
         */
        if (is_array($this->response->getOriginalContent())) {
            $this->fail($this->response->getOriginalContent()['error']);
        }

        $responseArray = $this->response->getOriginalContent()->jsonSerialize();

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertEquals('pending', $responseArray['status']);

        /**
         * Check if we've sent the correct params to biller
         */
        $transaction = $this->repository->findById($responseArray['transactionId'])->toArray();

        $requestBillerInteractions = FirestoreSerializer::getBillerInteractionsByType(
            $transaction['billerInteractions'],
            BillerInteraction::TYPE_REQUEST
        );

        $billerRequestPayload = end($requestBillerInteractions)['payload'];

        $this->assertArrayNotHasKey('Recurring1', $billerRequestPayload);
        $this->assertArrayNotHasKey('Recurring2', $billerRequestPayload);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_created_for_successful_sale_onecharge(): void
    {
        $this->markTestSkipped("We are skipping this Qysso related test because Qysso have issue with their URL. Until we receive valid URL from their end we will skip Qysso tests");

        $payload = self::payload();
        unset($payload['rebill']);
        unset($payload['tax']['rebillAmount']);

        $this->json('POST', $this->uri, $payload);

        /**
         * In case of error on the request, we received the message direct on the test case
         */
        if (is_array($this->response->getOriginalContent())) {
            $this->fail($this->response->getOriginalContent()['error']);
        }

        $responseArray = $this->response->getOriginalContent()->jsonSerialize();

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertEquals('pending', $responseArray['status']);

        /**
         * Check if we've sent the correct params to biller
         */
        $transaction = $this->repository->findById($responseArray['transactionId'])->toArray();

        $requestBillerInteractions = FirestoreSerializer::getBillerInteractionsByType(
            $transaction['billerInteractions'],
            BillerInteraction::TYPE_REQUEST
        );

        $billerRequestPayload = end($requestBillerInteractions)['payload'];

        $this->assertArrayNotHasKey('Recurring1', $billerRequestPayload);
        $this->assertArrayNotHasKey('Recurring2', $billerRequestPayload);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_created_for_successful_sale_with_recurring(): void
    {
        $this->markTestSkipped("We are skipping this Qysso related test because Qysso have issue with their URL. Until we receive valid URL from their end we will skip Qysso tests");

        $this->json('POST', $this->uri, self::payload());

        $responseArray = $this->response->getOriginalContent()->jsonSerialize();

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertEquals('pending', $responseArray['status']);

        $transaction = $this->repository->findById($responseArray['transactionId'])->toArray();

        $requestBillerInteractions = FirestoreSerializer::getBillerInteractionsByType(
            $transaction['billerInteractions'],
            BillerInteraction::TYPE_REQUEST
        );

        $billerRequestPayload = end($requestBillerInteractions)['payload'];

        $this->assertArrayHasKey('Recurring1', $billerRequestPayload);
        $this->assertArrayHasKey('Recurring2', $billerRequestPayload);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_not_found_when_client_id_is_invalid_or_missing_from_request(): void
    {
        $payload = self::payload();

        unset($payload['billerFields']['companyNum']);

        $response = $this->json(
            'POST',
            $this->uri,
            $payload
        );

        $response->assertResponseStatus(Response::HTTP_BAD_REQUEST);
    }
}
