<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Application\Services;

use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStartRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateStopRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateUpdateRebillCommandHandlerFactory;

class LaravelRocketgateUpdateRebillCommandHandlerFactory implements RocketgateUpdateRebillCommandHandlerFactory
{
    /**
     * @var Application
     */
    private $app;

    /**
     * LaravelPurchaseCommandHandlerFactory constructor.
     * @param Application $app Application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Request $request Request
     * @return array
     */
    public function getHandlerWithRebill(Request $request): array
    {
        if ($request->has('startRebill')) {
            return [
                'handler' => $this->app->make(PerformRocketgateStartRebillCommandHandler::class),
                'rebill'  => $request->json('startRebill')
            ];
        }

        if ($request->has('newRebill')) {
            return [
                'handler' => $this->app->make(PerformRocketgateUpdateRebillCommandHandler::class),
                'rebill'  => $request->json('newRebill')
            ];
        }

        if ($request->has('stopRebill') && $request->get('stopRebill') === true) {
            return [
                'handler' => $this->app->make(PerformRocketgateStopRebillCommandHandler::class),
                'rebill'  => []
            ];
        }

        return [
            'handler' => $this->app->make(PerformRocketgateStartRebillCommandHandler::class),
            'rebill'  => $request->json('startRebill')
        ];
    }
}
