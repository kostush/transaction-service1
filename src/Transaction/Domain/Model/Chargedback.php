<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Chargedback extends AbstractStatus
{
    const NAME = 'chargedback';

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
