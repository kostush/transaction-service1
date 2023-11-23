<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class CreditCard extends \Inacho\CreditCard
{
    private const EXCLUDE_MIR_FROM_MASTER_RANGE_PATTERN = '/^(5[0-5]|2[3-7]|22[1-9]|220[5-9])/';

    private const NEW_UNIONPAY_PATTERN = '/^(62|88|81[0-6]|817[0-1])/';

    /**
     * @var array[] $newCards that aren't supported by inacho library
     */
    protected static $mirCardPattern = [
            'mir' => [
                'type'      => 'mir',
                'pattern'   => '/^2200|2201|2202|2203|2204/',
                'length'    => [16, 17, 18, 19],
                'cvcLength' => [3],
                'luhn'      => true,
            ],
        ];

    /**
     * @param string|null $number credit card number
     * @param string|null $type   type of credit card
     *
     * @return array
     */
    public static function validCreditCard($number, $type = null): array
    {
        static::$cards = array_merge(static::$cards, static::$mirCardPattern);

        static::$cards["mastercard"]["pattern"] = self::EXCLUDE_MIR_FROM_MASTER_RANGE_PATTERN;

        static::$cards["unionpay"]["pattern"] = self::NEW_UNIONPAY_PATTERN;

        return parent::validCreditCard($number, $type);
    }
}
