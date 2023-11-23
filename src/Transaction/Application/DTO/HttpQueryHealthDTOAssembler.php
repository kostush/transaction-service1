<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

class HttpQueryHealthDTOAssembler implements TransactionHealthDTOAssembler
{
    /**
     * @param array $health Health array.
     * @return mixed|TransactionHealthQueryHttpDTO
     */
    public function assemble(array $health) : TransactionHealthQueryHttpDTO
    {
        return new TransactionHealthQueryHttpDTO($health);
    }
}
