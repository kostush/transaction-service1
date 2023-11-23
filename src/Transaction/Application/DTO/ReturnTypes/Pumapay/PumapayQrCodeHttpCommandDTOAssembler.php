<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\PumapayQrCodeTransactionDTOAssembler;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayQrCodeHttpCommandDTOAssembler implements PumapayQrCodeTransactionDTOAssembler
{
    /**
     * @param Transaction $transaction Transaction
     * @param string      $qrCode      QR code
     * @param string      $encryptText Encrypted text
     * @return mixed|RetrieveQrCodeCommandHttpDTO
     */
    public function assemble(Transaction $transaction, string $qrCode, string $encryptText)
    {
        return new RetrieveQrCodeCommandHttpDTO($transaction, $qrCode, $encryptText);
    }
}
