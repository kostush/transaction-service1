<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

class TransactionalCommandHandler implements CommandHandler
{
    /**
     * @var CommandHandler
     */
    private $handler;

    /**
     * @param CommandHandler       $handler Command Handler
     * @param TransactionalSession $session Transactional session
     */
    public function __construct(CommandHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Executes command atomically
     *
     * @param Command $command Command
     * @return mixed
     */
    public function execute(Command $command)
    {
        return $this->handler->execute($command);
    }
}
