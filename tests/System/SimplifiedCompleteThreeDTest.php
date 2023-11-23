<?php
declare(strict_types=1);

namespace Tests\System;

use Symfony\Component\HttpFoundation\Response;
use Tests\SystemTestCase;

class SimplifiedCompleteThreeDTest extends SystemTestCase
{
    /**
     * @var string
     */
    private $simplifiedCompleteThreeDUri = '/api/v2/transaction/{transactionId}/rocketgate/completeThreeD/session/bdeee6e5-5d45-4c0b-9511-d86c654dd77f';

    /**
     * @var array
     */
    private $simplifiedCompleteThreeDPayload;

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->simplifiedCompleteThreeDPayload = [
            'queryString' => 'flag=17c30f49482&id=3DS-Simplified&invoiceID=1632908872&hash=dQwDg2FEFDVBQ%2BxpWR0tqdA3Y0s%3D'
        ];
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_404_when_incorrect_transaction_id_is_provided(): void
    {
        $simplifiedCompleteThreeDUri = str_replace(
            '{transactionId}',
            $this->faker->uuid,
            $this->simplifiedCompleteThreeDUri
        );

        $response = $this->json('PUT', $simplifiedCompleteThreeDUri, $this->simplifiedCompleteThreeDPayload);
        $response->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}
