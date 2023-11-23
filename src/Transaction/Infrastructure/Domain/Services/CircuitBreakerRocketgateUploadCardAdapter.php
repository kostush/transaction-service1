<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\CardUploadAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\CompleteThreeDAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class CircuitBreakerRocketgateUploadCardAdapter extends CircuitBreaker implements CardUploadAdapter
{
    /**
     * @var RocketgateCardUploadAdapter
     */
    private $adapter;

    /**
     * CircuitBreakerRocketgateCompleteThreeDCreditCardAdapter constructor.
     * @param CommandFactory              $commandFactory    Command Factory
     * @param RocketgateCardUploadAdapter $cardUploadAdapter Complete ThreeD Adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateCardUploadAdapter $cardUploadAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $cardUploadAdapter;
    }

    /**
     * @param CardUploadCommand  $cardUploadCommand Complete ThreeD Command
     * @param \DateTimeImmutable $requestDate       Request date
     * @return RocketgateCreditCardBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function cardUpload(
        CardUploadCommand $cardUploadCommand,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse {

        Log::info('Send Rocketgate complete threeD request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateCardUploadCommand::class,
            $this->adapter,
            $cardUploadCommand,
            $requestDate
        );

        return $command->execute();
    }
}
