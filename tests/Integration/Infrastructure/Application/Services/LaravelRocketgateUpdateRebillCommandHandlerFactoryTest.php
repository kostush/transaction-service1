<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Application\Services;

use Illuminate\Http\Request;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStartRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStopRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Application\Services\LaravelRocketgateUpdateRebillCommandHandlerFactory;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\IntegrationTestCase;

class LaravelRocketgateUpdateRebillCommandHandlerFactoryTest extends IntegrationTestCase
{
    /**
     * @var LaravelRocketgateUpdateRebillCommandHandlerFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $rebill;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->factory = new LaravelRocketgateUpdateRebillCommandHandlerFactory($this->app);
        $this->rebill  = [
            'amount'    => 20,
            'start'     => 30,
            'frequency' => 365
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_an_array_when_new_rebill_is_provided(): array
    {
        $request = new Request();

        $data['newRebill'] = $this->rebill;

        $request->merge($data);

        $json = new ParameterBag($data);
        $request->setJson($json);

        $response = $this->factory->getHandlerWithRebill($request);

        $this->assertIsArray($response);

        return $response;
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_new_rebill_is_provided
     * @return void
     */
    public function it_should_contain_update_rebill_command_handler($response): void
    {
        $this->assertInstanceOf(PerformRocketgateUpdateRebillCommandHandler::class, $response['handler']);
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_new_rebill_is_provided
     * @return void
     */
    public function it_should_contain_correct_new_rebill_data($response): void
    {
        $this->assertSame($this->rebill, $response['rebill']);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_an_array_when_start_rebill_is_provided(): array
    {
        $request = new Request();

        $data['startRebill'] = $this->rebill;

        $request->merge($data);

        $json = new ParameterBag($data);
        $request->setJson($json);

        $response = $this->factory->getHandlerWithRebill($request);

        $this->assertIsArray($response);

        return $response;
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_start_rebill_is_provided
     * @return void
     */
    public function it_should_contain_start_rebill_command_handler($response): void
    {
        $this->assertInstanceOf(PerformRocketgateStartRebillCommandHandler::class, $response['handler']);
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_start_rebill_is_provided
     * @return void
     */
    public function it_should_contain_correct_start_rebill_data($response): void
    {
        $this->assertSame($this->rebill, $response['rebill']);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_an_array_when_stop_rebill_true_is_provided(): array
    {
        $request = new Request();

        $data['stopRebill'] = true;

        $request->merge($data);

        $json = new ParameterBag($data);
        $request->setJson($json);

        $response = $this->factory->getHandlerWithRebill($request);

        $this->assertIsArray($response);

        return $response;
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_stop_rebill_true_is_provided
     * @return void
     */
    public function it_should_contain_stop_rebill_command_handler($response): void
    {
        $this->assertInstanceOf(PerformRocketgateStopRebillCommandHandler::class, $response['handler']);
    }

    /**
     * @test
     * @param array $response Factory Response
     * @depends it_should_return_an_array_when_stop_rebill_true_is_provided
     * @return void
     */
    public function it_should_contain_empty_stop_rebill_data($response): void
    {
        $this->assertSame([], $response['rebill']);
    }
}
