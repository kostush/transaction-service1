<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;

interface LegacyPostbackResponseService
{
    /**
     * @param array  $payload    Payload
     * @param string $type       Type
     * @param int    $statusCode Status code
     * @return LegacyPostbackBillerResponse
     */
    public function translate(
        array $payload,
        string $type,
        int $statusCode
    ): LegacyPostbackBillerResponse;
}
