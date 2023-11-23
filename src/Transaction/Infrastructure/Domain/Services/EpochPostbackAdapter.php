<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Epoch\Application\Services\PostbackTranslateCommand;
use ProBillerNG\Epoch\Application\Services\PostbackTranslateCommandHandler;
use ProBillerNG\Transaction\Domain\Services\EpochPostbackAdapter as EpochAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;

class EpochPostbackAdapter implements EpochAdapterInterface
{
    /** @var PostbackTranslateCommandHandler */
    protected $epochPostbackHandler;

    /** @var EpochTranslator */
    protected $translator;

    /**
     * EpochPostbackAdapter constructor.
     * @param PostbackTranslateCommandHandler $epochPostbackHandler Postback Handler
     * @param EpochTranslator                 $translator           Epoch Postback Translator
     */
    public function __construct(PostbackTranslateCommandHandler $epochPostbackHandler, EpochTranslator $translator)
    {
        $this->epochPostbackHandler = $epochPostbackHandler;
        $this->translator           = $translator;
    }

    /**
     * @param array  $payload         Payload postback data
     * @param string $transactionType Transaction Type
     * @param string $digestKey       The digest key used by the epoch library to validate digest
     * @return EpochBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function getTranslatedPostback(
        array $payload,
        string $transactionType,
        string $digestKey
    ): EpochBillerResponse {
        $requestDate = new \DateTimeImmutable();

        // Send Epoch translate postback request
        $response = $this->epochPostbackHandler->execute(
            new PostbackTranslateCommand($payload, $transactionType, $digestKey)
        );

        $responseDate = new \DateTimeImmutable();

        return $this->translator->translate($response, $requestDate, $responseDate);
    }
}
