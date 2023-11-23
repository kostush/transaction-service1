<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PerformRocketgateSimplifiedCompleteThreeDCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $queryString;

    /**
     * PerformRocketgateCompleteThreeDCommand constructor.
     * @param string $transactionId Transaction id
     * @param string $queryString   Query string
     * @throws Exception
     * @throws MissingChargeInformationException
     */
    public function __construct(
        string $transactionId,
        string $queryString
    ) {
        $this->initTransactionId($transactionId);
        $this->initQueryString($queryString);
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
    public function queryString(): string
    {
        return $this->queryString;
    }

    /**
     * @param mixed $transactionId Transaction Id
     * @return void
     * @throws Exception
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
     * @param string $queryString Query string
     * @return void
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    private function initQueryString(string $queryString): void
    {
        if (empty($queryString)) {
            throw new MissingChargeInformationException('query string');
        }

        $this->queryString = $queryString;
    }
}
