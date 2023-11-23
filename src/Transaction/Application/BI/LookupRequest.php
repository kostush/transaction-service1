<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\BI;

use Carbon\Carbon;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;

class LookupRequest extends BaseEvent
{
    public const TYPE           = 'Purchase_3DS_Lookup';
    public const LATEST_VERSION = '1';

    /**
     * LookupRequest constructor.
     * @param RocketgateBillerResponse|null $billerResponse BillerResponse
     * @throws \Exception
     */
    public function __construct(?RocketgateBillerResponse $billerResponse)
    {
        parent::__construct(self::TYPE);

        $biEventValue = [
            'version'         => self::LATEST_VERSION,
            'timestamp'       => Carbon::now()->format('Y-m-d H:i:s'),
            'sessionId'       => Log::getSessionId(),
            'three_d_version' => $billerResponse->threedsVersion()
        ];

        $this->setValue($biEventValue);
    }
}
