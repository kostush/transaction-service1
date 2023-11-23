<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Log;
use ProBillerNG\Qysso\Application\BillerFields;
use ProBillerNG\Qysso\Application\NewSaleCommand;
use ProBillerNG\Qysso\Application\NewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Services\QyssoNewSaleAdapter as QyssoAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;

class QyssoNewSaleAdapter implements QyssoAdapterInterface
{
    /** @var NewSaleCommandHandler */
    protected $qyssoNewSaleHandler;

    /** @var QyssoTranslator */
    protected $translator;

    /**
     * @param NewSaleCommandHandler $qyssoNewSaleHandler New Sale Handler
     * @param QyssoTranslator       $translator          Qysso Postback Translator
     */
    public function __construct(NewSaleCommandHandler $qyssoNewSaleHandler, QyssoTranslator $translator)
    {
        $this->qyssoNewSaleHandler = $qyssoNewSaleHandler;
        $this->translator          = $translator;
    }

    /**
     * @param NewSaleCommand      $newSaleCommand Sale Command
     * @param QyssoBillerSettings $billerSettings
     * @return QyssoNewSaleBillerResponse
     */
    public function newSale(
        NewSaleCommand $newSaleCommand,
        QyssoBillerSettings $billerSettings
    ): QyssoNewSaleBillerResponse {
        $requestDate = new \DateTimeImmutable();

        // Send Qysso newSale request
        $response = $this->qyssoNewSaleHandler->execute(
            $newSaleCommand,
            new BillerFields($billerSettings->companyNum(), $billerSettings->personalHashKey())
        );

        Log::info("QyssoResponse", $response->toArray());

        $responseDate = new \DateTimeImmutable();

        return $this->translator->translateNewSale($response, $requestDate, $responseDate);
    }
}
