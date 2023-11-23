<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Epoch\Code;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;

abstract class EpochBillerResponse extends BillerResponse
{
    /**
     * @var array
     */
    protected static $return400Codes = [
        Code::EPOCH_POSTBACK_INVALID_PAYLOAD,
        Code::EPOCH_INVALID_TYPE_RECEIVED,
        Code::EPOCH_INVALID_DIGEST_KEY,
        Code::EPOCH_MISSING_PAYLOAD,
        Code::EPOCH_INVALID_DIGEST_VALIDATION,
        Code::EPOCH_MISSING_NEW_SALE_INFORMATION,
    ];

    /**
     * @return bool
     */
    public function shouldReturn400(): bool
    {
        return in_array($this->code(), static::$return400Codes);
    }
}
