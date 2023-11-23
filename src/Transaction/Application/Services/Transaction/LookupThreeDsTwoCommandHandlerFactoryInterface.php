<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use Illuminate\Http\Request;

interface LookupThreeDsTwoCommandHandlerFactoryInterface
{
    /**
     * @param Request $request Request
     * @return BaseCommandHandler
     */
    public function getHandler(Request $request): BaseCommandHandler;
}
