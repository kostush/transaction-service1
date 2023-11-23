<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;

interface EpochPostbackAdapter extends EpochAdapter
{
    /**
     * @param array $payload         Payload postback data
     * @param string $transactionType Transaction Type
     * @param string $digestKey       The digest key used by the epoch library to validate digest
     * @return EpochBillerResponse
     */
    public function getTranslatedPostback(
        array $payload,
        string $transactionType,
        string $digestKey
    ): EpochBillerResponse;
}
