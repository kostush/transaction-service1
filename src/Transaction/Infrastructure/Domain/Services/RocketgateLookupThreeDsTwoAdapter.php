<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommand;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommandHandler;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\LookupThreeDsTwoAdapter;

class RocketgateLookupThreeDsTwoAdapter implements LookupThreeDsTwoAdapter
{
    /**
     * @var ThreeDSTwoLookupCommandHandler
     */
    private $lookupCommandHandler;

    /**
     * @var RocketgateLookupThreeDsTwoTranslator
     */
    protected $translator;

    /**
     * RocketgateLookupAdapter constructor.
     * @param ThreeDSTwoLookupCommandHandler       $lookupCommandHandler Lookup command handler
     * @param RocketgateLookupThreeDsTwoTranslator $translator           Translator
     */
    public function __construct(
        ThreeDSTwoLookupCommandHandler $lookupCommandHandler,
        RocketgateLookupThreeDsTwoTranslator $translator
    ) {
        $this->lookupCommandHandler = $lookupCommandHandler;
        $this->translator           = $translator;
    }

    /**
     * @param ThreeDSTwoLookupCommand $command     ThreeDSTwoLookupCommand
     * @param \DateTimeImmutable      $requestDate Request date
     * @return BillerResponse
     * @throws Exception\InvalidBillerResponseException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Rocketgate\Application\Exceptions\InvalidCommandException
     */
    public function performLookup(
        ThreeDSTwoLookupCommand $command,
        \DateTimeImmutable $requestDate
    ): BillerResponse {
        // call rocketgate service library
        $response = $this->lookupCommandHandler->execute($command);

        // Call the translator
        return $this->translator->toRocketgateLookupBillerResponse(
            $response,
            $requestDate,
            new \DateTimeImmutable()
        );
    }
}
