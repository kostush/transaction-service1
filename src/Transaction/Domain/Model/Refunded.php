<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Refunded extends AbstractStatus
{
    const NAME = 'refunded';

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function refund(): AbstractStatus
    {
        return Refunded::create();
    }

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function chargeback(): AbstractStatus
    {
        return Chargedback::create();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::NAME;
    }
}
