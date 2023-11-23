<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingTransactionInformationException;

abstract class AddBillerInteractionForRebillCommand extends Command
{
    /**
     * @var string
     */
    private $previousTransactionId;

    /**
     * @var array
     */
    private $payload;

    /**
     * AddBillerInteractionForRebillOnPumapayCommand constructor.
     * @param null|string $previousTransactionId Previous transaction Id
     * @param null|array  $payload               Payload
     * @throws MissingTransactionInformationException
     */
    public function __construct(
        ?string $previousTransactionId,
        ?array $payload
    ) {
        $this->initPreviousTransactionId($previousTransactionId);
        $this->initPayload($payload);
    }

    /**
     * @param string|null $previousTransactionId Previous transaction Id
     * @return void
     * @throws MissingTransactionInformationException
     */
    private function initPreviousTransactionId(?string $previousTransactionId): void
    {
        if (empty($previousTransactionId)) {
            throw new MissingTransactionInformationException('previousTransactionId');
        }

        $this->previousTransactionId = $previousTransactionId;
    }

    /**
     * @param array|null $payload Payload
     * @return void
     * @throws MissingTransactionInformationException
     */
    private function initPayload(?array $payload): void
    {
        if (empty($payload)) {
            throw new MissingTransactionInformationException('payload');
        }

        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function previousTransactionId(): string
    {
        return $this->previousTransactionId;
    }

    /**
     * @return array
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
