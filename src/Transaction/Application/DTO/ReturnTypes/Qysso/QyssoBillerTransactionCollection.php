<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Domain\Collection;

class QyssoBillerTransactionCollection extends Collection
{
    /**
     * {@inheritdoc}
     * @param mixed $object object
     * @return bool
     */
    protected function isValidObject($object)
    {
        return $object instanceof QyssoBillerTransaction;
    }
}
