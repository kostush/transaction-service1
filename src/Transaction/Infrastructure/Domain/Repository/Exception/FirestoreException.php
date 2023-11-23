<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Repository\Exception;

use ProBillerNG\Transaction\Infrastructure\InfrastructureException as InfrastructureException;
use ProBillerNG\Transaction\Code;

class FirestoreException extends InfrastructureException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::FIRESTORE_EXCEPTION;
}
