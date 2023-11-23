<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RocketgateCancelRebillController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * CreateTransactionController constructor
     * @param PerformRocketgateCancelRebillCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateCancelRebillCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('Begin cancel rebill transaction creation');

        try {
            $command = new PerformRocketgateCancelRebillCommand(
                (string) $request->input('transactionId'),
                (string) $request->input('merchantId'),
                (string) $request->input('merchantPassword'),
                (string) $request->input('merchantCustomerId'),
                (string) $request->input('merchantInvoiceId')
            );

            $result = $this->handler->execute($command);

            if ($result->shouldReturn400()) {
                return response()->json($result, Response::HTTP_BAD_REQUEST);
            }

            return response()->json($result, Response::HTTP_CREATED);

        } catch (InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (TransactionCreationException | Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
