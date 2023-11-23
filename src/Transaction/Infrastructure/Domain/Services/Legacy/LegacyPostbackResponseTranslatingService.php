<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy;

use ProBillerNG\Transaction\Domain\Services\LegacyPostbackResponseService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;

class LegacyPostbackResponseTranslatingService implements LegacyPostbackResponseService
{
    /**
     * @param array  $payload    Payload
     * @param string $type       Type
     * @param int    $statusCode Status code
     * @return LegacyPostbackBillerResponse
     * @throws \Exception
     */
    public function translate(
        array $payload,
        string $type,
        int $statusCode
    ): LegacyPostbackBillerResponse {
        return LegacyPostbackBillerResponse::create($payload, $type, $statusCode);
    }
}
