<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;

interface PumapayPostbackAdapter extends PumapayAdapter
{
    /**
     * @param string $payload         Payload postback data
     * @param string $transactionType Transaction Type
     * @return PumapayBillerResponse
     */
    public function getTranslatedPostback(string $payload, string $transactionType): PumapayBillerResponse;
}
