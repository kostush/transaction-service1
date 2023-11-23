<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class Declined extends AbstractStatus
{
    const NAME = 'declined';

    /**
     * {@inheritdoc}
     *
     * @return AbstractStatus
     */
    public function decline(): AbstractStatus
    {
        return Declined::create();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::NAME;
    }
}
