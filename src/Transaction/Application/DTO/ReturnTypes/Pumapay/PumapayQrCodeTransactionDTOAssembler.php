<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Domain\Model\Transaction;

interface PumapayQrCodeTransactionDTOAssembler extends TransactionDTOAssembler
{
    /**
     * Assembles DTO from Transaction aggregate
     *
     * @param Transaction $transaction Transaction object
     * @param string      $qrCode      QR code
     * @param string      $encryptText Encrypted text
     * @return mixed
     */
    public function assemble(Transaction $transaction, string $qrCode, string $encryptText);
}
