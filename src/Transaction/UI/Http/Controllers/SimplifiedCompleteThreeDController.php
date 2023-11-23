<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateSimplifiedCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use Throwable;

class SimplifiedCompleteThreeDController extends Controller
{
    /**
     * @var PerformRocketgateSimplifiedCompleteThreeDCommandHandler
     */
    protected $handler;

    /**
     * RocketgateNewCreditCardSaleController constructor
     * @param PerformRocketgateSimplifiedCompleteThreeDCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateSimplifiedCompleteThreeDCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param string  $transactionId Transaction Id
     * @param Request $request       Request
     * @return JsonResponse
     * @throws Exception
     */
    public function completeTransaction(string $transactionId, Request $request): JsonResponse
    {
        Log::info('Begin complete threeD transaction');

        try {
            $command = new PerformRocketgateSimplifiedCompleteThreeDCommand(
                $transactionId,
                (string) $request->input('queryString')
            );

            $result = $this->handler->execute($command);

            return response()->json($result);

        } catch (InvalidStatusException | InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (TransactionCreationException | Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
