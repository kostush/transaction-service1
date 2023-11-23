<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Application\Services\BillerResponseAttributeExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class EpochNewSaleCommandHttpDTO implements \JsonSerializable
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
            $this->response['redirectUrl'] = $this->getAttribute($transactionForMainPurchase, 'redirectURL');
            $this->buildCrossSalesResponse();
            return;
        }

        if (!$transactionForMainPurchase->status()->pending()) {
            $this->response['code']   = $transactionForMainPurchase->code();
            $this->response['reason'] = $transactionForMainPurchase->reason();
        }
    }

    /**
     * @return void
     */
    private function buildCrossSalesResponse(): void
    {
        // omit the main transaction and get the rest (crossSales)
        /** @var Transaction $crossSalesTransactions */
        $crossSalesTransactions = array_slice($this->transactions, 1);

        foreach ($crossSalesTransactions as $crossSalesTransaction) {
            $this->response['crossSales'][] = [
                'transactionId' => (string) $crossSalesTransaction->transactionId(),
                'status'        => (string) $crossSalesTransaction->status(),
            ];
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
