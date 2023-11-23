<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Legacy;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;

/**
 * Class PerformLegacyNewSaleCommand
 * @package ProBillerNG\Transaction\Application\Services\Transaction\Legacy
 */
class PerformLegacyNewSaleCommand extends Command
{
    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string|null
     */
    private $paymentMethod;

    /** @var Member|null */
    private $member;

    /**
     * @var array
     */
    private $charges;

    /**
     * @var int|null
     */
    private $legacyMemberId;

    /**
     * @var string
     */
    private $returnUrl;

    /**
     * @var array|null
     */
    private $others;

    /**
     * @var string
     */
    private $billerName;

    /**
     * @var string
     */
    private $postbackUrl;

    /**
     * PerformLegacyNewSaleCommand constructor.
     * @param string      $paymentType    Payment Type
     * @param array       $charges        Charges
     * @param string      $returnUrl      Return Url
     * @param string      $postbackUrl    Postback Url
     * @param string      $billerName     Biller Name
     * @param string|null $paymentMethod  Payment Method
     * @param Member|null $member         Member
     * @param int         $legacyMemberId Legacy Member Id
     * @param array|null  $others         Others
     */
    public function __construct(
        string $paymentType,
        array $charges,
        string $returnUrl,
        string $postbackUrl,
        string $billerName,
        ?string $paymentMethod,
        ?Member $member,
        ?int $legacyMemberId,
        ?array $others
    ) {
        $this->paymentType    = $paymentType;
        $this->paymentMethod  = $paymentMethod;
        $this->member         = $member;
        $this->charges        = $charges;
        $this->legacyMemberId = $legacyMemberId;
        $this->returnUrl      = $returnUrl;
        $this->others         = $others;
        $this->postbackUrl    = $postbackUrl;
        $this->billerName     = $billerName;
    }

    /**
     * @return string
     */
    public function paymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return Member
     */
    public function member(): ?Member
    {
        return $this->member;
    }

    /**
     * @return array
     */
    public function charges(): array
    {
        return $this->charges;
    }

    /**
     * @return int|null
     */
    public function legacyMemberId(): ?int
    {
        return $this->legacyMemberId;
    }

    /**
     * @return string
     */
    public function returnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @return array|null
     */
    public function others(): ?array
    {
        return $this->others;
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
    public function postbackUrl(): string
    {
        return $this->postbackUrl;
    }
}
