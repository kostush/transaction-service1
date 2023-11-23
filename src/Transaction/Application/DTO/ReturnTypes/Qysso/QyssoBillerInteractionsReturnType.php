<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionRequestResponseInteractionTrait;

class QyssoBillerInteractionsReturnType
{
    use TransactionRequestResponseInteractionTrait;

    /**
     * @var QyssoBillerTransactionCollection
     */
    private $billerTransactions;

    /**
     * QyssoBillerInteractionsReturnType constructor.
     * @param QyssoBillerTransactionCollection $billerTransactionCollection The biller transactions collection
     */
    private function __construct(
        QyssoBillerTransactionCollection $billerTransactionCollection
    ) {
        $this->billerTransactions = $billerTransactionCollection;
    }

    /**
     * @param mixed       $billerInteractions The biller interactions object
     * @param string      $type
     * @param string|null $initialBillerTransactionId
     * @param array       $requestPayload
     * @return QyssoBillerInteractionsReturnType
     */
    public static function createFromBillerInteractionsCollection(
        $billerInteractions,
        string $type,
        ?string $initialBillerTransactionId,
        array $requestPayload
    ): self {
        $billerTransactionCollection = new QyssoBillerTransactionCollection();

        if (!$billerInteractions->count()) {
            return new static($billerTransactionCollection);
        }

        $interactions = $billerInteractions->toArray();
        self::sortBillerInteractions($interactions);

        self::buildResponseData(
            $billerTransactionCollection,
            self::getResponseInteractions($interactions),
            $requestPayload['CompanyNum'] ?? '',
            $type,
            $initialBillerTransactionId
        );

        return new static($billerTransactionCollection);
    }

    /**
     * @param QyssoBillerTransactionCollection $billerTransactionCollection The biller transaction collection
     * @param array                            $responseInteractions        The biller interaction object
     * @param string                           $companyNum
     * @param string                           $type
     * @param string|null                      $initialBillerTransactionId
     */
    public static function buildResponseData(
        QyssoBillerTransactionCollection $billerTransactionCollection,
        array $responseInteractions,
        string $companyNum,
        string $type,
        ?string $initialBillerTransactionId
    ) {
        $lastInteraction = end($responseInteractions);
        $payload         = null;

        if (!empty($lastInteraction)) {
            $payload = json_decode($lastInteraction->payload(), true);
            //add the first interaction
            $billerTransactionCollection->add(
                self::buildQyssoBillerTransaction(
                    $payload,
                    $companyNum,
                    $type,
                    $initialBillerTransactionId
                )
            );
        }
    }


    /**
     * @param array       $payload The biller Interaction
     * @param string      $companyNum
     * @param string      $type
     * @param string|null $initialBillerTransactionId
     * @return QyssoBillerTransaction
     */
    private static function buildQyssoBillerTransaction(
        array $payload,
        string $companyNum,
        string $type,
        ?string $initialBillerTransactionId
    ): QyssoBillerTransaction {
        return new QyssoBillerTransaction(
            $companyNum,
            $type,
            $payload['trans_id'] ?? $payload['TransID'],
            $payload,
            $initialBillerTransactionId
        );
    }

    /**
     * @return QyssoBillerTransactionCollection
     */
    public function billerTransactions(): QyssoBillerTransactionCollection
    {
        return $this->billerTransactions;
    }
}
