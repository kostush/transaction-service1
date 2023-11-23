<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Pumapay\Application\Services\CancelCommand;
use ProBillerNG\Pumapay\Application\Services\CancelCommandHandler;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\NoBillerInteractionsException;
use ProBillerNG\Transaction\Domain\Services\PumapayCancelRebillAdapter as PumapayAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;

class PumapayCancelRebillAdapter implements PumapayAdapterInterface
{
    /**
     * @var CancelCommandHandler
     */
    protected $pumapayCancelRebillHandler;

    /**
     * @var PumapayTranslator
     */
    protected $translator;

    /**
     * PumapayPostbackAdapter constructor.
     * @param CancelCommandHandler $pumapayCancelRebillHandler Command handler
     * @param PumapayTranslator    $translator                 Pumapay Postback Translator
     */
    public function __construct(CancelCommandHandler $pumapayCancelRebillHandler, PumapayTranslator $translator)
    {
        $this->pumapayCancelRebillHandler = $pumapayCancelRebillHandler;
        $this->translator                 = $translator;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $businessId  Business Id
     * @param string            $apiKey      Api key
     * @return PumapayBillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws NoBillerInteractionsException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function cancelRebill(
        ChargeTransaction $transaction,
        string $businessId,
        string $apiKey
    ): PumapayBillerResponse {
        $requestDate = new \DateTimeImmutable();
        $payload     = $this->getPayload($transaction);

        // Send Pumapay cancel rebill request
        $response = $this->pumapayCancelRebillHandler->execute(
            new CancelCommand($apiKey, $businessId, $payload)
        );

        $responseDate = new \DateTimeImmutable();

        return $this->translator->toCancelRebillBillerResponse($response, $requestDate, $responseDate);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return string
     * @throws NoBillerInteractionsException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function getPayload(ChargeTransaction $transaction): string
    {
        $billerInteractionForJoinTransaction = $transaction->billerInteractions()->filter(
            function (BillerInteraction $billerInteraction) {
                return $billerInteraction->isResponseType();
            }
        )->toArray();

        if ($transaction->billerInteractions()->count() === 0) {
            throw new NoBillerInteractionsException((string) $transaction->transactionId());
        }

        // sort by createdAt to get the biller interaction for join transaction
        usort(
            $billerInteractionForJoinTransaction,
            function ($firstBillerInteraction, $secondBillerInteraction) {
                return $firstBillerInteraction->createdAt() < $secondBillerInteraction->createdAt();
            }
        );

        /** @var BillerInteraction $billerInteraction */
        $billerInteraction = $billerInteractionForJoinTransaction[0];

        return $billerInteraction->payload();
    }
}
