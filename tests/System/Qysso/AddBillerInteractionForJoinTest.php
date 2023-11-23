<?php
declare(strict_types=1);

namespace Tests\System\Qysso;

use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

/**
 * Class AddBillerInteractionForJoinTest
 * @package Tests\System\Qysso
 * @group   qysso
 */
class AddBillerInteractionForJoinTest extends SystemTestCase
{
    /** @var array */
    protected $payload;

    /**
     * @var string
     */
    private $uri = '/api/v1/transaction/{transactionId}/qysso/billerInteraction';

    /**
     * @dataProvider possible_biller_interactions
     * @test
     *
     * @param string $expectedStatus status
     * @param string $payloadFile    file
     * @param string $personalHashKey
     */
    public function it_should_return_correct_response_given_the_biller_interaction2(
        string $expectedStatus,
        string $payloadFile,
        string $personalHashKey
    ) {

        $this->markTestSkipped("We are skipping this Qysso related test because Qysso have issue with their URL. Until we receive valid URL from their end we will skip Qysso tests");

        /**
         * Create the first transaction
         */
        $initialRequest = $this->json(
            'POST',
            '/api/v1/sale/qysso',
            QyssoNewSaleTest::payload($personalHashKey),
            ['X-CORRELATION-ID' => '3f36c1f8-b9d4-4193-89a6-334e012be8dd']
        );

        $initialRequest = json_decode($initialRequest->response->getContent(), true);
        $transactionId  = $initialRequest['transactionId'];

        /**
         * Perform the add biller interaction Request
         */
        $payload = json_decode(file_get_contents(__DIR__ . '/' . $payloadFile), true);
        $uri     = str_replace('{transactionId}', $transactionId, $this->uri);

        $this->json('PUT', $uri, $payload);
        $responseContent = json_decode($this->response->getContent(), true);

        /**
         * Assert the return is correct
         */
        $this->assertResponseStatus(Response::HTTP_OK);
        $this->assertSame($expectedStatus, $responseContent['status']);

        /**
         * Check database for a valid transaction updated
         */
        $repository         = $this->app->make(TransactionRepository::class);
        $updatedTransaction = $repository->findById($transactionId);

        $this->assertSame($expectedStatus, (string) $updatedTransaction->status());
    }

    /**
     * @return array
     */
    public function possible_biller_interactions(): array
    {
        return [
            'approved from return'   => [
                'approved',
                'qysso_approved_biller_interaction.json',
                'SEK83K2Z2D'
            ],
            'declined from return'   => [
                'declined',
                'qysso_declined_biller_interaction.json',
                'SEK83K2Z2D'
            ],
            'approved from postback' => [
                'approved',
                'qysso_approved_biller_interaction_from_postback.json',
                'SEK83K2Z2D'
            ],
            'declined from postback' => [
                'declined',
                'qysso_declined_biller_interaction_from_postback.json',
                'F2CRZKA3F6'
            ],
        ];
    }
}
