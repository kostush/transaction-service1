<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochPostbackBillerResponse;

interface EpochService
{
    /**
     * @param array  $payload         Payload
     * @param string $transactionType Transaction Type
     * @param string $digestKey       Digest Key
     * @return EpochPostbackBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function translatePostback(
        array $payload,
        string $transactionType,
        string $digestKey
    ): EpochPostbackBillerResponse;

    /**
     * @param array  $transactions The Transactions Array
     * @param array  $taxArray     The Tax array
     * @param string $sessionId    The Session Id
     * @param Member $member       Member
     * @return EpochNewSaleBillerResponse
     */
    public function chargeNewSale(
        array $transactions,
        array $taxArray,
        string $sessionId,
        Member $member
    ): EpochNewSaleBillerResponse;
}
