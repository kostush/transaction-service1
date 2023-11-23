<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class Payment
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var Information
     */
    public $information;

    /**
     * Information constructor.
     * @param string      $type        Type
     * @param Information $information Information
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(?string $type, Information $information)
    {
        $this->initType($type);
        $this->information = $information;
    }

    /**
     * @param null|string $type Type
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    private function initType(?string $type): void
    {
        if (empty($type)) {
            throw new MissingChargeInformationException('type');
        }
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * @return Information
     */
    public function information(): Information
    {
        return $this->information;
    }
}
