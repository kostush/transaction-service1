<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Pumapay\Code;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;

abstract class PumapayBillerResponse extends BillerResponse
{
    /**
     * @var array
     */
    protected static $return400PumapayCodes = [
        Code::PUMAPAY_POSTBACK_INVALID_STATUS_ID,
        Code::PUMAPAY_POSTBACK_INVALID_TYPE_ID,
        Code::PUMAPAY_POSTBACK_MISSING_STATUS_ID,
        Code::PUMAPAY_POSTBACK_MISSING_TYPE_ID,
        Code::PUMAPAY_POSTBACK_MISSING_PAYLOAD,
        Code::PUMAPAY_POSTBACK_INVALID_PAYLOAD,
        Code::PUMAPAY_INVALID_TYPE_PROVIDED,
        Code::PUMAPAY_INVALID_TYPE_RECEIVED,
    ];

    /**
     * @return bool
     */
    public function shouldReturn400(): bool
    {
        $shouldReturn400 = false;

        if (in_array($this->code(), static::$return400PumapayCodes)) {
            $shouldReturn400 = true;
        }

        return $shouldReturn400;
    }
}
