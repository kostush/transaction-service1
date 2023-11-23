<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

class QyssoBillerTransaction
{
    /**
     * @var string
     */
    private $companyNum;

    /**
     * @var null|string
     */
    private $billerTransactionId;

    /**
     * @var array
     */
    private $rawBillerResponse;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $initialBillerTransactionId;

    /**
     * BillerTransaction constructor.
     * @param string      $companyNum                 Company number.
     * @param string      $type                       Transaction type.
     * @param null|string $billerTransactionId        The qysso transaction id.
     * @param array       $rawBillerResponse          Biller response.
     * @param string|null $initialBillerTransactionId Initial biller transaction id.
     */
    public function __construct(
        string $companyNum,
        string $type,
        string $billerTransactionId,
        array $rawBillerResponse,
        ?string $initialBillerTransactionId
    ) {
        $this->companyNum                 = $companyNum;
        $this->type                       = $type;
        $this->billerTransactionId        = $billerTransactionId;
        $this->rawBillerResponse          = $rawBillerResponse;
        $this->initialBillerTransactionId = $initialBillerTransactionId;
    }

    /**
     * @return string
     */
    public function companyNum(): string
    {
        return $this->companyNum;
    }

    /**
     * @return string
     */
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function billerTransactionId(): ?string
    {
        return $this->billerTransactionId;
    }

    /**
     * @return array
     */
    public function rawBillerResponse(): array
    {
        return $this->rawBillerResponse;
    }

    /**
     * @return null|string
     */
    public function initialBillerTransactionId(): ?string
    {
        return $this->initialBillerTransactionId;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $billerTransaction = [
            "companyNum"           => $this->companyNum(),
            "billerTransactionId" => $this->billerTransactionId(),
            "rawBillerResponse"   => $this->rawBillerResponse(),
            "type"                  => $this->type()
        ];

        if ($this->initialBillerTransactionId() !== null) {
            $billerTransaction ['initialBillerTransactionId'] = $this->initialBillerTransactionId();
        }

        return $billerTransaction;
    }
}
