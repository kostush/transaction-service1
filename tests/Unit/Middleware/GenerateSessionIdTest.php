<?php


namespace Tests\Unit\Middleware;

use App\Http\Middleware\GenerateSessionId;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\UnitTestCase;

class GenerateSessionIdTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function it_should_generate_sessionId()
    {
        $request = new Request();

        $middleWare = new GenerateSessionId();

        $middleWare->handle($request, function () {});

        $this->assertNotEmpty($request->get('sessionId'));

        return $request->get('sessionId');
    }

    /**
     * @test
     * @depends it_should_generate_sessionId
     * @param string $sessionId string
     */
    public function it_should_generate_valid_sessionId(string $sessionId)
    {
        $this->assertTrue(Uuid::isValid($sessionId));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_should_not_override_the_existing_sessionId()
    {
        $sessionId = '24e9912f-cb91-4ff4-b779-08352bbc4ee9';
        $request   = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('route')
            ->with('sessionId')
            ->willReturn($sessionId);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method('get')->with('sessionId')->willReturn($sessionId);

        $middleWare = new GenerateSessionId();
        $middleWare->handle($request, function () {});

        $this->assertEquals($request->attributes->get('sessionId'), $sessionId);

        return $request->get('sessionId');
    }

}
