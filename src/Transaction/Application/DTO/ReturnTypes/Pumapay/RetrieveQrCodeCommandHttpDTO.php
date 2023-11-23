<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay;

use ProBillerNG\Transaction\Domain\Model\Transaction;

class RetrieveQrCodeCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var string
     */
    protected $qrCode;

    /**
     * @var string
     */
    protected $encryptText;

    /**
     * RetrieveQrCodeCommandHttpDTO constructor.
     * @param Transaction $transaction Transaction
     * @param string      $qrCode      QR code
     * @param string      $encryptText Encrypted text
     */
    public function __construct(Transaction $transaction, string $qrCode, string $encryptText)
    {
        $this->transaction = $transaction;
        $this->qrCode      = $qrCode;
        $this->encryptText = $encryptText;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $response = [
            'transactionId' => (string) $this->transaction->transactionId(),
            'status'        => (string) $this->transaction->status()
        ];

        if ($this->transaction->status()->pending()) {
            $response['qrCode']      = $this->qrCode;
            $response['encryptText'] = $this->encryptText;
        } else {
            $response['code']  = $this->transaction->code();
            $response['error'] = $this->transaction->reason();
        }

        return $response;
    }
}
