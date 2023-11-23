<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Exception;

use ProBillerNG\Transaction\Application\Services\Query;
use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Code;

class InvalidQueryException extends ApplicationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_QUERY_GIVEN;

    /**
     * InvalidCommandException constructor.
     *
     * @param string $expecting Expected $query class name
     * @param Query  $query     Query given
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $expecting, Query $query)
    {
        parent::__construct(null, $expecting, get_class($query));
    }
}
