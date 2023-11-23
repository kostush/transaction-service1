<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Rocketgate\UpdateRebillClient;

abstract class UpdateRebillAdapter
{
    /**
     * @var UpdateRebillClient
     */
    protected $client;

    /**
     * @var RocketgateCreditCardChargeTranslator
     */
    protected $translator;

    /**
     * RocketgateCreditCardChargeAdapter constructor.
     * @param UpdateRebillClient                   $rocketgate Client
     * @param RocketgateCreditCardChargeTranslator $translator Translator
     */
    public function __construct(UpdateRebillClient $rocketgate, RocketgateCreditCardChargeTranslator $translator)
    {
        $this->client     = $rocketgate;
        $this->translator = $translator;
    }
}
