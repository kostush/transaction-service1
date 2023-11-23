<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\Services\BillerResponseAttributeExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class QyssoNewSaleCommandHttpDTO implements \JsonSerializable
{
    use BillerResponseAttributeExtractorTrait;

    /**
     * @var array
     */
    protected $transactions;

    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $transactions Transactions array
     */
    public function __construct(array $transactions)
    {
        $this->transactions = $transactions;
        $this->initTransaction();
    }

    /**
     * @return void
     */
    private function initTransaction(): void
    {
        /** @var Transaction $transactionForMainPurchase */
        $transactionForMainPurchase = $this->transactions[0];

        $this->response['transactionId'] = (string) $transactionForMainPurchase->transactionId();
        $this->response['status']        = (string) $transactionForMainPurchase->status();

        if ($transactionForMainPurchase->status()->pending()) {
            $this->response['redirectUrl'] = $this->getAttribute($transactionForMainPurchase, 'D3Redirect');
            return;
        }

        if (!$transactionForMainPurchase->status()->pending()) {
            $this->response['code']   = $transactionForMainPurchase->code();
            $this->response['reason'] = $transactionForMainPurchase->reason();
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->response;
    }
}
