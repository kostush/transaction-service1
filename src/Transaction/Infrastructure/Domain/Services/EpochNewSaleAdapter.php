<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Epoch\Application\Services\NewSaleCommand;
use ProBillerNG\Epoch\Application\Services\NewSaleCommandHandler;
use ProBillerNG\Transaction\Domain\Services\EpochNewSaleAdapter as EpochAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;

class EpochNewSaleAdapter implements EpochAdapterInterface
{
    /** @var NewSaleCommandHandler */
    protected $epochNewSaleHandler;

    /** @var EpochTranslator */
    protected $translator;

    /**
     * @param NewSaleCommandHandler $epochNewSaleHandler New Sale Handler
     * @param EpochTranslator       $translator          Epoch Postback Translator
     */
    public function __construct(NewSaleCommandHandler $epochNewSaleHandler, EpochTranslator $translator)
    {
        $this->epochNewSaleHandler = $epochNewSaleHandler;
        $this->translator          = $translator;
    }

    /**
     * @param NewSaleCommand $newSaleCommand Sale Command
     * @return EpochNewSaleBillerResponse
     * @throws \Exception
     */
    public function newSale(NewSaleCommand $newSaleCommand): EpochNewSaleBillerResponse
    {
        $requestDate = new \DateTimeImmutable();

        // Send Epoch newSale request
        $response = $this->epochNewSaleHandler->execute($newSaleCommand);

        $responseDate = new \DateTimeImmutable();

        return $this->translator->translateNewSale($response, $requestDate, $responseDate);
    }
}
