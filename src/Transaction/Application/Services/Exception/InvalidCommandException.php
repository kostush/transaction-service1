<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Exception;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Code;

class InvalidCommandException extends ApplicationException
{
    /**
     * @var int $code Error code
     */
    protected $code = Code::INVALID_COMMAND_GIVEN;

    /**
     * InvalidCommandException constructor.
     *
     * @param string  $expecting Expected command class name
     * @param Command $command   Command given
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(string $expecting, Command $command)
    {
        parent::__construct(null, $expecting, get_class($command));
    }
}
