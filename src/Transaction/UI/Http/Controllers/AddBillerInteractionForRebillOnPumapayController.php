<?php

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForRebillOnPumapayCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException;
use Throwable;

class AddBillerInteractionForRebillOnPumapayController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * AddBillerInteractionForRebillOnPumapayController constructor.
     * @param AddBillerInteractionForRebillOnPumapayCommandHandler $handler Handler
     */
    public function __construct(AddBillerInteractionForRebillOnPumapayCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function add(Request $request): JsonResponse
    {
        Log::info('Begin add biller interaction for rebill on PumaPay');

        try {
            $command = new AddBillerInteractionForRebillOnPumapayCommand(
                $request->input('previousTransactionId', ''),
                $request->input('payload', [])
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (
            PreviousTransactionNotFoundException |
            InvalidPayloadException |
            InvalidCommandException |
            InfrastructureException $e
        ) {
            return $this->badRequest($e);
        } catch (Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
