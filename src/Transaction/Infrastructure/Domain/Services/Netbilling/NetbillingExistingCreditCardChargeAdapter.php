<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\NetbillingChargeAdapter;

class NetbillingExistingCreditCardChargeAdapter implements NetbillingChargeAdapter
{
    /**
     * @var NetbillingClient
     */
    protected $client;

    /**
     * @var NetbillingCreditCardChargeTranslator
     */
    protected $translator;


    /**
     * NetbillingNewCreditCardChargeAdapter constructor.
     * @param NetbillingClient                     $netbilling Client
     * @param NetbillingCreditCardChargeTranslator $translator Translator
     */
    public function __construct(
        NetbillingClient $netbilling,
        NetbillingCreditCardChargeTranslator $translator
    ) {
        $this->client     = $netbilling;
        $this->translator = $translator;
    }

    /**
     * @param CreditCardChargeCommand $command     Command
     * @param \DateTimeImmutable      $requestDate Request date
     * @return NetbillingBillerResponse
     * @throws \Exception
     */
    public function charge(
        CreditCardChargeCommand $command,
        \DateTimeImmutable $requestDate
    ): NetbillingBillerResponse {


        $response     = $this->client->chargeExistingCreditCard($command);
        $responseDate = new \DateTimeImmutable();

        // Call the translator
        $billerResponse = $this->translator->toCreditCardBillerResponse(
            $response,
            $requestDate,
            $responseDate
        );

        // return result
        return $billerResponse;
    }
}
