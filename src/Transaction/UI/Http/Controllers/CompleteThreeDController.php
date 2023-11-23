<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCompleteThreeDCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidStatusException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use Throwable;

class CompleteThreeDController extends Controller
{
    /**
     * @var PerformRocketgateCompleteThreeDCommandHandler
     */
    protected $handler;

    /**
     * RocketgateNewCreditCardSaleController constructor
     * @param PerformRocketgateCompleteThreeDCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateCompleteThreeDCommandHandler $handler)
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
            $command = new PerformRocketgateCompleteThreeDCommand(
                $transactionId,
                (string) $request->input('pares'),
                (string) $request->input('md')
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
