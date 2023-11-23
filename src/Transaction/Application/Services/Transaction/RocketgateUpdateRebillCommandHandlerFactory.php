<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use Illuminate\Http\Request;

interface RocketgateUpdateRebillCommandHandlerFactory
{
    /**
     * @param Request $request Request
     * @return array
     */
    public function getHandlerWithRebill(Request $request): array;
}
