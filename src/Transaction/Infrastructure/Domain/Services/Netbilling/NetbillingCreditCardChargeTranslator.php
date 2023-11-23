<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;

class NetbillingCreditCardChargeTranslator
{
    /**
     * @param string            $responsePayload Response from netbilling
     * @param DateTimeImmutable $requestDate     Request date of transaction
     * @param DateTimeImmutable $responseDate    Response date of transaction
     * @return NetbillingBillerResponse Response from netbilling
     * @throws InvalidBillerResponseException
     * @throws LoggerException
     */
    public function toCreditCardBillerResponse(
        string $responsePayload,
        DateTimeImmutable $requestDate,
        DateTimeImmutable $responseDate
    ): NetbillingBillerResponse {
        Log::info('Creating charge response from Netbilling response');

        try {
            return NetbillingBillerResponse::create($requestDate, $responsePayload, $responseDate);
        } catch (Exception $e) {
            throw new InvalidBillerResponseException();
        }
    }
}
