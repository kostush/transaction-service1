<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ErrorException;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\MapField;

/**
 * Class representation of the netbilling unified error structure.
 */
class NetbillingUnifiedGroupError
{
    public const BILLER       = 'biller';
    public const AUTH_MESSAGE = 'authMessage';
    public const PROCESSOR    = 'processor';

    /**
     * @var string
     */
    protected $biller = 'netbilling';

    /**
     * @var string
     */
    protected $authMessage;

    /**
     * @var string
     */
    protected $processor;

    /**
     * @return MapField
     *
     * @throws ErrorException
     */
    public function generateMapField(): MapField
    {
        $mapField = new MapField(GPBType::STRING, GPBType::STRING);

        $mapField->offsetSet(self::BILLER, $this->biller);

        if ($this->hasAuthMessage()) {
            $mapField->offsetSet(self::AUTH_MESSAGE, $this->authMessage);
        }

        if ($this->hasProcessor()) {
            $mapField->offsetSet(self::PROCESSOR, $this->processor);
        }

        return $mapField;
    }

    /**
     * @param string $authMessage
     *
     * @return self
     */
    public function setAuthMessage(string $authMessage): self
    {
        $this->authMessage = $authMessage;

        return $this;
    }

    /**
     * @param string $processor
     *
     * @return self
     */
    public function setProcessor(string $processor): self
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAuthMessage(): bool
    {
        return isset($this->authMessage);
    }

    /**
     * @return bool
     */
    public function hasProcessor(): bool
    {
        return isset($this->processor);
    }
}
