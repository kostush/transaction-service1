<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\TransactionRequestResponseInteractionTrait;

class EpochBillerInteractionsReturnType
{
    use TransactionRequestResponseInteractionTrait;
    /**
     * @var EpochBillerTransactionCollection
     */
    private $billerTransactions;

    /**
     * @var string
     */
    private $paymentSubType;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $zip;

    /**
     * @var string|null
     */
    private $country;

    /**
     * RocketgateBillerTransactionsReturnType constructor.
     * @param EpochBillerTransactionCollection $billerTransactionCollection The biller transactions collection
     * @param string                           $paymentSubType              The Epoch payment subtype
     * @param string|null                      $email                       The member email
     * @param string|null                      $name                        The member name
     * @param string|null                      $zip                         The member zip code
     * @param string|null                      $country                     The member country
     */
    private function __construct(
        EpochBillerTransactionCollection $billerTransactionCollection,
        string $paymentSubType,
        ?string $email,
        ?string $name,
        ?string $zip,
        ?string $country
    ) {
        $this->billerTransactions = $billerTransactionCollection;
        $this->paymentSubType     = $paymentSubType;
        $this->email              = $email;
        $this->name               = $name;
        $this->zip                = $zip;
        $this->country            = $country;
    }

    /**
     * @param mixed $billerInteractions The biller interactions object
     * @return self
     */
    public static function createFromBillerInteractionsCollection($billerInteractions): self
    {
        $billerTransactionCollection = new EpochBillerTransactionCollection();
        $paymentSubType              = '';
        if (!$billerInteractions->count()) {
            return new static($billerTransactionCollection, $paymentSubType);
        }

        $interactions = $billerInteractions->toArray();
        self::sortBillerInteractions($interactions);

        list($paymentSubType, $email, $name, $zip, $country,) = self::buildResponseData(
            $billerTransactionCollection,
            self::getResponseInteractions($interactions)
        );

        return new static($billerTransactionCollection, $paymentSubType, $email, $name, $zip, $country);
    }

    /**
     * @param EpochBillerTransactionCollection $billerTransactionCollection The biller transaction collection
     * @param array                            $responseInteractions        The biller interaction object
     * @return array
     */
    public static function buildResponseData(
        EpochBillerTransactionCollection $billerTransactionCollection,
        array $responseInteractions
    ): array {
        $lastInteraction = end($responseInteractions);
        $payload         = null;

        if (!empty($lastInteraction)) {
            $payload = json_decode($lastInteraction->payload());
            //add the first interaction
            $billerTransactionCollection->add(
                self::buildEpochBillerTransaction(
                    $payload
                )
            );
        }

        $name = isset($payload->response) ? (property_exists($payload->response, 'name') ? $payload->response->name : '') : '';
        $zip  = isset($payload->response) ? (property_exists($payload->response, 'zip') ? $payload->response->zip : '') : '';

        return [
            isset($payload->response) ? $payload->response->payment_subtype : '',
            isset($payload->response) ? $payload->response->email : '',
            $name,
            $zip,
            isset($payload->response) ? $payload->response->country : '',
        ];
    }


    /**
     * @param \stdClass $payload The biller Interaction
     * @return EpochBillerTransaction
     */
    private static function buildEpochBillerTransaction(
        \stdClass $payload
    ): EpochBillerTransaction {
        return new EpochBillerTransaction(
            $payload->response->pi_code ?? null,
            $payload->response->member_id ?? null,
            $payload->response->transaction_id ?? null,
            $payload->response->ans ?? null
        );
    }

    /**
     * @return EpochBillerTransactionCollection
     */
    public function billerTransactions(): EpochBillerTransactionCollection
    {
        return $this->billerTransactions;
    }


    /**
     * @return string
     */
    public function paymentSubType(): string
    {
        return $this->paymentSubType;
    }

    /**
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function zip(): ?string
    {
        return $this->zip;
    }

    /**
     * @return string|null
     */
    public function country(): ?string
    {
        return $this->country;
    }
}
