<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;

interface QyssoService
{
    /**
     * @param string      $jsonPayload     The payload coming from qysso
     * @param string      $personalHashKey The merchant's hash key
     * @param string|null $transactionType The type of the transaction
     * @return QyssoPostbackBillerResponse
     */
    public function translatePostback(
        string $jsonPayload,
        string $personalHashKey,
        ?string $transactionType = null
    ): QyssoBillerResponse;

    /**
     * @param array  $transactions The Transactions Array
     * @param array  $taxArray     The Tax array
     * @param string $sessionId    The Session Id
     * @param string $clientIp     Client ip
     * @param Member $member       Member
     * @return QyssoNewSaleBillerResponse
     */
    public function chargeNewSale(
        array $transactions,
        array $taxArray,
        string $sessionId,
        string $clientIp,
        Member $member
    ): QyssoNewSaleBillerResponse;
}
