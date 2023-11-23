<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Legacy;

use ProBillerNG\Transaction\Application\Services\Command;

class AddBillerInteractionForJoinOnLegacyCommand extends Command
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
     * @var int
     */
    private $statusCode;
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $siteId;

    /**
     * AddBillerInteractionForJoinOnLegacyCommand constructor.
     * @param string      $transactionId Transaction Id
     * @param array       $payload       Payload
     * @param int         $statusCode    Status Code
     * @param string      $type          Type
     * @param string|null $siteId        Site Id.
     */
    public function __construct(
        string $transactionId,
        array $payload,
        int $statusCode,
        string $type,
        ?string $siteId
    ) {
        $this->transactionId = $transactionId;
        $this->payload       = $payload;
        $this->statusCode    = $statusCode;
        $this->type          = $type;
        $this->siteId        = $siteId;
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
     * @return int
     */
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function siteId(): ?string
    {
        return $this->siteId;
    }
}
