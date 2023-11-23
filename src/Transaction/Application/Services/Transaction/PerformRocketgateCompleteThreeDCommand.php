<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PerformRocketgateCompleteThreeDCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string|null
     */
    private $pares;

    /**
     * @var string|null
     */
    private $md;

    /**
     * PerformRocketgateCompleteThreeDCommand constructor.
     * @param string $transactionId Transaction id
     * @param string $pares         Pares
     * @param string $md            Rocketgate biller transaction id
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionId,
        string $pares,
        string $md
    ) {
        $this->initTransactionId($transactionId);
        $this->initParesAndMd($pares, $md);
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string|null
     */
    public function pares(): ?string
    {
        return $this->pares;
    }

    /**
     * @return string|null
     */
    public function md(): ?string
    {
        return $this->md;
    }

    /**
     * @param mixed $transactionId Transaction Id
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new MissingChargeInformationException('transactionId');
        }

        $this->transactionId = $transactionId;
    }

    /**
     * @param string $pares Pares
     * @param string $md    Rocketgate biller transaction id
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initParesAndMd(string $pares, string $md): void
    {
        if (empty($pares) && empty($md)) {
            throw new MissingChargeInformationException('pares or md');
        }

        if (!empty($pares)) {
            $this->pares = $pares;
        }

        if (!empty($md)) {
            $this->md = $md;
        }
    }
}
