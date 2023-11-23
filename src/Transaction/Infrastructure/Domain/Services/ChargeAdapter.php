<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingClient;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;

abstract class ChargeAdapter
{
    /**
     * @var ChargeClient
     */
    protected $client;

    /**
     * @var RocketgateCreditCardChargeTranslator
     */
    protected $translator;

    /**
     * RocketgateCreditCardChargeAdapter constructor.
     * @param ChargeClient                         $rocketgate Client
     * @param RocketgateCreditCardChargeTranslator $translator Translator
     */
    public function __construct(ChargeClient $rocketgate, RocketgateCreditCardChargeTranslator $translator)
    {
        $this->client     = $rocketgate;
        $this->translator = $translator;
    }
}
