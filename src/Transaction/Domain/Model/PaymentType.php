<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use MyCLabs\Enum\Enum;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\BI\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentTypeException;

class PaymentType extends Enum
{
    const CREDIT_CARD = 'cc';

    public const CRYPTO = 'crypto';

    //Coinpayment
    public const LEGACY_CRYPTO = 'cryptocurrency';

    //CentroBill
    public const ALIPAY        = 'alipay';
    public const ELV           = 'elv';
    public const MCB           = 'mcb';
    public const PAYSAFECARD   = 'paysafecard';
    public const SEPA          = 'sepa';
    public const SKRILL        = 'skrill';
    public const SOFORTBANKING = 'sofortbanking';
    public const UNIONPAY      = 'unionpay';
    public const WECHAT        = 'wechat';

    //Check
    public const CHECK = 'check';

    //Paygarden
    public const GIFTCARD = 'giftcard';

    //Others payments
    public const EWALLET        = 'ewallet';
    public const BANKTRANSFER   = 'banktransfer';
    public const PREPAYWALLET   = 'prepaywallet';
    public const CHECKS         = 'checks';
    public const CRYPTOCURRENCY = 'cryptocurrency';
    public const GIFTCARDS      = 'giftcards';

    /**
     * @param string $paymentType Payment Type
     * @return PaymentType
     * @throws InvalidPaymentTypeException
     * @throws Exception
     */
    public static function create(string $paymentType): self
    {
        try {
            return new self(
                strtolower($paymentType)
            );
        } catch (\UnexpectedValueException $e) {
            throw new InvalidPaymentTypeException($paymentType);
        }
    }

    /**
     * @return array
     */
    public static function biPaymentTypeMapping(): array
    {
        $possiblesPaymentTypes = array();

        foreach (self::toArray() as $paymentType) {
            $possiblesPaymentTypes[$paymentType] = $paymentType;
        }

        //Credit card has a different name on BI Events
        $possiblesPaymentTypes[self::CREDIT_CARD] = BaseEvent::BI_CREDIT_CARD_TYPE;

        return $possiblesPaymentTypes;
    }
}
