<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

interface CommandHandler
{
    /**
     * Executes a command
     *
     * @param Command $command Command
     * @return mixed
     */
    public function execute(Command $command);
}
