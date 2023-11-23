<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use Exception;

class LegacyBillerChargeSettings implements BillerSettings
{
    /**
     * @var int
     */
    private $legacyMemberId;

    /**
     * @var string
     */
    private $billerName;

    /**
     * @var string
     */
    private $returnUrl;

    /**
     * @var string
     */
    private $postbackUrl;

    /**
     * @var array
     */
    private $others;

    /**
     * LegacyBillerChargeSettings constructor.
     * @param int|null   $legacyMemberId Legacy Member Id.
     * @param string     $billerName     biller name
     * @param string     $returnUrl      return Url
     * @param string     $postbackUrl    postback urlCharge.php
     * @param array|null $others         Others
     */
    private function __construct(
        ?int $legacyMemberId,
        string $billerName,
        string $returnUrl,
        string $postbackUrl,
        ?array $others
    ) {
        $this->legacyMemberId = $legacyMemberId;
        $this->billerName     = $billerName;
        $this->returnUrl      = $returnUrl;
        $this->postbackUrl    = $postbackUrl;
        $this->others         = $others;
    }

    /**
     * @param int|null   $legacyMemberId Legacy Member Id
     * @param string     $billerName     Biller Name
     * @param string     $returnUrl      Return Url
     * @param string     $postbackUrl    Postback url
     * @param array|null $others         Others
     * @return LegacyBillerChargeSettings
     */
    public static function create(
        ?int $legacyMemberId,
        string $billerName,
        string $returnUrl,
        string $postbackUrl,
        ?array $others = null
    ): self {
        return new static(
            $legacyMemberId,
            $billerName,
            $returnUrl,
            $postbackUrl,
            $others
        );
    }

    /**
     * @return int
     */
    public function legacyMemberId(): ?int
    {
        return $this->legacyMemberId;
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return $this->billerName;
    }

    /**
     * @return string
     */
    public function returnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @return string
     */
    public function postbackUrl(): string
    {
        return $this->postbackUrl;
    }

    /**
     * @return array|null
     */
    public function others(): ?array
    {
        return $this->others;
    }

    /**
     * @param string $transactionId Transaction Id to identify the transaction on payment gateway
     * @return void
     */
    public function addTransactionIdToCustomFields(string $transactionId): void
    {
        if (!isset($this->others['custom'])) {
            $this->others['custom'] = [];
        }

        $this->others['custom'] += [
            'transactionId' => $transactionId
        ];
    }


    /**
     * @param int $mainProductId Product Id to identify it is cross sale on postback step
     * @return void
     */
    public function addMainProductIdToCustomFields(int $mainProductId): void
    {
        if (!isset($this->others['custom'])) {
            $this->others['custom'] = [];
        }

        $this->others['custom'] += [
            'mainProductId' => $mainProductId
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'legacyMemberId' => $this->legacyMemberId(),
            'billerName'     => $this->billerName(),
            'returnUrl'      => $this->returnUrl(),
            'postbackUrl'    => $this->postbackUrl(),
            'others'         => $this->others(),
        ];
    }

    /**
     * @param array $data retrieved data
     * @return mixed
     * @throws Exception
     */
    public static function createFromArray(array $data): self
    {
        return static::create(
            $data['legacyMemberId'] ?? null,
            $data['billerName'],
            $data['returnUrl'],
            $data['postbackUrl'],
            $data['others'] ?? null
        );
    }
}
