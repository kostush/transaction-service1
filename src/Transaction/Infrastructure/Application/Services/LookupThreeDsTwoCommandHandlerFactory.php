<?php

namespace ProBillerNG\Transaction\Infrastructure\Application\Services;

use Illuminate\Http\Request;
use Laravel\Lumen\Application;
use ProBillerNG\Transaction\Application\Services\Transaction\BaseCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateLookupThreeDsTwoCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommandHandlerFactoryInterface;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;

class LookupThreeDsTwoCommandHandlerFactory implements LookupThreeDsTwoCommandHandlerFactoryInterface
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
     * @return BaseCommandHandler
     */
    public function getHandler(Request $request): BaseCommandHandler
    {
        switch ($request->route('billerName')) {
            case strtolower(BillerSettings::ROCKETGATE):
                return $this->app->make(RocketgateLookupThreeDsTwoCommandHandler::class);
            default:
                return $this->app->make(RocketgateLookupThreeDsTwoCommandHandler::class);
        }
    }
}
