<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;

class AddBillerInteractionForJoinOnQyssoCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $payload;

    /**
     * @param string      $transactionId Transaction Id
     * @param string|null $payload       Payload
     * @throws MissingTransactionInformationException
     */
    public function __construct(
        string $transactionId,
        ?string $payload
    ) {
        $this->transactionId = $transactionId;
        $this->initPayload($payload);
    }

    /**
     * @param string|null $payload Payload
     * @return void
     * @throws MissingTransactionInformationException
     */
    private function initPayload(?string $payload): void
    {
        if (empty($payload)) {
            throw new MissingTransactionInformationException('payload');
        }

        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function payload(): string
    {
        return $this->payload;
    }
}
