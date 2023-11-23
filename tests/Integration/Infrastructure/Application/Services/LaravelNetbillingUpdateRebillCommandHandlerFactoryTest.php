<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Application\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Application\Services\LaravelNetbillingUpdateRebillCommandHandlerFactory;
use Tests\IntegrationTestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class LaravelNetbillingUpdateRebillCommandHandlerFactoryTest extends IntegrationTestCase
{
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

        $this->factory = new LaravelNetbillingUpdateRebillCommandHandlerFactory($this->app);
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
    public function it_should_return_an_array_when_update_rebill_is_provided(): array
    {
        $request = new Request();

        $data['updateRebill'] = $this->rebill;

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
     * @depends it_should_return_an_array_when_update_rebill_is_provided
     * @return void
     */
    public function it_should_contain_update_rebill_command_handler($response): void
    {
        $this->assertInstanceOf(PerformNetbillingUpdateRebillCommandHandler::class, $response['handler']);
    }
}