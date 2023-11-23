<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class PumaPayChargeSettings extends PumaPayBillerSettings
{
    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * PumaPayChargeSettings constructor.
     * @param string      $businessId    PumaPay business Id
     * @param string      $businessModel Business model
     * @param string      $apiKey        Api Key
     * @param string|null $title         Title
     * @param string|null $description   Description
     */
    private function __construct(
        string $businessId,
        string $businessModel,
        string $apiKey,
        ?string $title,
        ?string $description
    ) {
        $this->businessId    = $businessId;
        $this->businessModel = $businessModel;
        $this->apiKey        = $apiKey;
        $this->title         = $title;
        $this->description   = $description;
    }

    /**
     * @param string      $businessId    PumaPay business Id
     * @param string      $businessModel Business model
     * @param string      $apiKey        Api Key
     * @param string|null $title         Title
     * @param string|null $description   Description
     * @return PumaPayChargeSettings
     */
    public static function create(
        string $businessId,
        string $businessModel,
        string $apiKey,
        ?string $title,
        ?string $description
    ): self {
        return new static(
            $businessId,
            $businessModel,
            $apiKey,
            $title,
            $description
        );
    }

    /**
     * @return string|null
     */
    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'title'       => $this->title(),
            'description' => $this->description(),
        ]);
    }
}
