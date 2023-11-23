<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Collection;

use ProBillerNG\Transaction\Domain\Collection;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;

class BillerInteractionCollection extends Collection
{
    /**
     * {@inheritdoc}
     * @param mixed $object object
     * @return bool
     */
    protected function isValidObject($object)
    {
        return $object instanceof BillerInteraction;
    }
}
