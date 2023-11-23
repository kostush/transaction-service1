<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;

class AddBillerInteractionForJoinOnEpochCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var array
     */
    private $payload;

    /**
     * @param string $transactionId Transaction Id
     * @param array  $payload       Payload
     * @throws MissingTransactionInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionId,
        ?array $payload
    ) {
        $this->transactionId = $transactionId;
        $this->initPayload($payload);
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return array
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @param array|null $payload Payload
     * @return void
     * @throws MissingTransactionInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initPayload(?array $payload): void
    {
        if (empty($payload)) {
            throw new MissingTransactionInformationException('payload');
        }

        $this->payload = $payload;
    }
}
