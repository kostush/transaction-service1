<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Ramsey\Uuid\Uuid;

class SiteId extends Id
{
    /**
     * @param Uuid|null $value Uuid
     * @return self
     * @throws \Exception
     */
    public static function create(Uuid $value = null): Id
    {
        return parent::create($value);
    }

    /**
     * @param string|null $value string
     * @return self
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function createFromString(?string $value): Id
    {
        try {
            return parent::createFromString($value);
        } catch (\Exception $e) {
            throw new InvalidChargeInformationException('siteId');
        }
    }
}
