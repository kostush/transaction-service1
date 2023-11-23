<?php
declare(strict_types=1);

namespace Tests\System\Middleware;

use App\Logger;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Log;
use Ramsey\Uuid\Uuid;
use Tests\SystemTestCase;

class LoggerTest extends SystemTestCase
{
    use Logger;

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function logger_should_take_tracking_fields_from_request_and_set_them_in_right_logger_fields(): void
    {
        $correlationId = $this->faker->uuid;
        $sessionId     = $this->faker->uuid;
        $request       = new Request();

        $request->attributes->set('sessionId', $sessionId);
        $request->headers->set('X-CORRELATION-ID', $correlationId);
        $this->initLogger('test', $request);
        $this->assertEquals(Log::getCorrelationId(), $correlationId);
        $this->assertEquals(Log::getSessionId(), $sessionId);
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_create_a_correlation_id_same_as_session_id_when_we_send_a_correlation_id(): void
    {
        $correlationId = $this->faker->uuid;
        $request       = new Request();

        $request->headers->set('X-CORRELATION-ID', $correlationId);
        $this->initLogger('test', $request);
        $this->assertEquals(Log::getCorrelationId(), Log::getSessionId());
    }
}
