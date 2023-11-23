<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

/**
 * Class DeclinedBillerResponseExtraDataNetbilling
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
class DeclinedBillerResponseExtraDataNetbilling extends DeclinedBillerResponseExtraData
{
    /**
     * @var string
     */
    protected $processor;

    /**
     * @var string
     */
    protected $authMessage;

    /**
     * @param string $processor
     */
    public function setProcessor(string $processor): void
    {
        $this->processor = $processor;
    }

    /**
     * @param string $authMessage
     */
    public function setAuthMessage(string $authMessage): void
    {
        $this->authMessage = $authMessage;
    }

    /**
     * @return string
     */
    public function getProcessor(): string
    {
        return $this->processor;
    }

    /**
     * @return string
     */
    public function getAuthMessage(): string
    {
        return $this->authMessage;
    }
}
