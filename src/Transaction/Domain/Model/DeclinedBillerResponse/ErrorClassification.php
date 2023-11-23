<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

/**
 * Class ErrorClassification
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
class ErrorClassification
{
    // Fallback for error classification not found.
    const DEFAULT_GROUP_DECLINE      = '9999';
    const DEFAULT_ERROR_TYPE         = 'Error';
    const DEFAULT_GROUP_MESSAGE      = 'Group decline message not configured';
    const DEFAULT_RECOMMENDED_ACTION = 'Recommended action is not configured';

    /**
     * This object is just to make sure we have this data available for the ErrorClassification.
     * We're not using it for other purposes other than logging.
     *
     * @var MappingCriteria
     */
    private $mappingCriteria;

    /**
     * @var string
     */
    private $groupDecline;

    /**
     * @var string
     */
    private $errorType;

    /**
     * @var string
     */
    private $groupMessage;

    /**
     * @var string
     */
    private $recommendedAction;

    /**
     * ErrorClassification constructor.
     *
     * @param MappingCriteria                      $mappingCriteria
     * @param DeclinedBillerResponseExtraData|null $extraData
     */
    public function __construct(MappingCriteria $mappingCriteria, ?DeclinedBillerResponseExtraData $extraData)
    {
        $this->mappingCriteria = $mappingCriteria->toArray();

        // We are going the fallback when no error classification is found for a specific criteria
        if (empty($extraData)) {
            $this->groupDecline      = self::DEFAULT_GROUP_DECLINE;
            $this->errorType         = self::DEFAULT_ERROR_TYPE;
            $this->groupMessage      = self::DEFAULT_GROUP_MESSAGE;
            $this->recommendedAction = self::DEFAULT_RECOMMENDED_ACTION;

            return;
        }

        $this->groupDecline      = $extraData->groupDecline();
        $this->errorType         = $extraData->errorType();
        $this->groupMessage      = $extraData->groupMessage();
        $this->recommendedAction = $extraData->recommendedAction();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'errorClassification' => get_object_vars($this),
        ];
    }
}
