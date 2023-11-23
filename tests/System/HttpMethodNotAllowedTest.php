<?php

namespace Tests\System;

use Illuminate\Http\Response;
use Tests\SystemTestCase;

class HttpMethodNotAllowedTest extends SystemTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_return_200_when_successful(): void
    {
        $response = $this->get(
            '/api/v1/transaction/' . $this->faker->uuid . '/pumapay/billerInteraction',
            []
        );
        $response->assertResponseStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
