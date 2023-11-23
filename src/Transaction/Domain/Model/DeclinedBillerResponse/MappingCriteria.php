<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

/**
 * Class MappingCriteria
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
abstract class MappingCriteria
{
    /**
     * @var string
     */
    protected $billerName;

    /**
     * @return string
     */
    public function billerName(): string
    {
        return $this->billerName;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
