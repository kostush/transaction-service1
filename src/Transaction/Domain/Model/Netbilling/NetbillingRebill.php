<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Netbilling;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Rebill;

class NetbillingRebill extends Rebill
{
    /**
     * @param int|null $frequency Frequency
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function initFrequency(?int $frequency): void
    {
        if (is_int($frequency) && !($frequency >= 0)) {
            throw new InvalidChargeInformationException('rebill => frequency');
        }

        $this->frequency = (int) $frequency;
    }
}