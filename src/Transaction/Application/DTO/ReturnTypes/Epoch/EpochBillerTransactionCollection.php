<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Domain\Collection;

class EpochBillerTransactionCollection extends Collection
{
    /**
     * {@inheritdoc}
     * @param mixed $object object
     * @return bool
     */
    protected function isValidObject($object)
    {
        return $object instanceof EpochBillerTransaction;
    }
}
