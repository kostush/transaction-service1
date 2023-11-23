<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Pumapay\Application\Services\PostbackCommand;
use ProBillerNG\Pumapay\Application\Services\PostbackCommandHandler;
use ProBillerNG\Transaction\Domain\Services\PumapayPostbackAdapter as PumapayAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;

class PumapayPostbackAdapter implements PumapayAdapterInterface
{

    protected $pumapayPostbackHandler;


    protected $translator;

    /**
     * PumapayPostbackAdapter constructor.
     * @param PostbackCommandHandler $pumapayPostbackHandler Postback Handler
     * @param PumapayTranslator      $translator             Pumapay Postback Translator
     */
    public function __construct(PostbackCommandHandler $pumapayPostbackHandler, PumapayTranslator $translator)
    {
        $this->pumapayPostbackHandler = $pumapayPostbackHandler;
        $this->translator             = $translator;
    }

    /**
     * @param string $payload         Payload postback data
     * @param string $transactionType Transaction Type
     * @return PumapayBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    public function getTranslatedPostback(string $payload, string $transactionType): PumapayBillerResponse
    {
        $requestDate = new \DateTimeImmutable();

        // Send Pumapay translate postback request
        $response = $this->pumapayPostbackHandler->execute(
            new PostbackCommand($payload, $transactionType)
        );

        $responseDate = new \DateTimeImmutable();

        return $this->translator->translate($response, $requestDate, $responseDate);
    }
}
