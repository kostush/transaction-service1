<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

interface TransactionHealthDTOAssembler
{
    /**
     * Assemble transaction health response
     *
     * @param array $health Health array.
     * @return mixed
     */
    public function assemble(array $health);
}
