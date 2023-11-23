<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Application\Services;

use Illuminate\Http\Request;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\NetbillingUpdateRebillCommandHandlerFactory;
use Laravel\Lumen\Application;

class LaravelNetbillingUpdateRebillCommandHandlerFactory implements NetbillingUpdateRebillCommandHandlerFactory
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
        if ($request->has('updateRebill')) {
            return [
                'handler' => $this->app->make(PerformNetbillingUpdateRebillCommandHandler::class),
                'rebill'  => $request->json('updateRebill')
            ];
        }

        return [
            'handler' => $this->app->make(PerformNetbillingUpdateRebillCommandHandler::class),
            'rebill'  => $request->json('startRebill')
        ];
    }
}