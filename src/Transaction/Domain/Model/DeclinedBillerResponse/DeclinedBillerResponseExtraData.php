<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

/**
 * Class DeclinedBillerResponseExtraData
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
abstract class DeclinedBillerResponseExtraData
{
    /**
     * @var string
     */
    protected $groupDecline;

    /**
     * @var string
     */
    protected $errorType;

    /**
     * @var string
     */
    protected $groupMessage;

    /**
     * @var string
     */
    protected $recommendedAction;

    /**
     * @return string
     */
    public function groupDecline(): string
    {
        return $this->groupDecline;
    }

    /**
     * @return string
     */
    public function errorType(): string
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function groupMessage(): string
    {
        return $this->groupMessage;
    }

    /**
     * @return string
     */
    public function recommendedAction(): string
    {
        return $this->recommendedAction;
    }

    /**
     * @param string $groupDecline Group Decline
     * @return void
     */
    public function setGroupDecline(string $groupDecline): void
    {
        $this->groupDecline = $groupDecline;
    }

    /**
     * @param string $errorType Error Type
     * @return void
     */
    public function setErrorType(string $errorType): void
    {
        $this->errorType = $errorType;
    }

    /**
     * @param string $groupMessage Group Message
     * @return void
     */
    public function setGroupMessage(string $groupMessage): void
    {
        $this->groupMessage = $groupMessage;
    }

    /**
     * @param string $recommendedAction Recommended action
     * @return void
     */
    public function setRecommendedAction(string $recommendedAction): void
    {
        $this->recommendedAction = $recommendedAction;
    }
}
