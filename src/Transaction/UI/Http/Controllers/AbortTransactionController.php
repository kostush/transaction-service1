<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use Illuminate\Http\Response;
use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AbortTransactionCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use Throwable;

class AbortTransactionController extends Controller
{
    /**
     * @var AbortTransactionCommandHandler
     */
    protected $handler;

    /**
     * CreateTransactionController constructor
     *
     * @param AbortTransactionCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(AbortTransactionCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param string $transactionId Transaction Id
     * @return JsonResponse|Response|ResponseFactory
     * @throws Exception
     */
    public function abort(string $transactionId)
    {

        try {
            Log::info('Begin transaction retrieval process', ['transactionId' => $transactionId]);

            $command = new AbortTransactionCommand(
                $transactionId
            );
            $result  = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_OK);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (TransactionAlreadyProcessedException|InvalidTransactionInformationException $e) {
            return $this->badRequest($e);
        } catch (Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
