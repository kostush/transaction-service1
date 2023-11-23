<?php

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\AddBillerInteractionForJoinOnPumapayCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException;
use Throwable;

class AddBillerInteractionForJoinOnPumapayController extends Controller
{
    /** @var TransactionalCommandHandler */
    protected $handler;

    /**
     * AddBillerInteractionForJoinOnPumapayController constructor.
     * @param AddBillerInteractionForJoinOnPumapayCommandHandler $handler Handler
     */
    public function __construct(AddBillerInteractionForJoinOnPumapayCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param string  $transactionId Transaction Id
     * @param Request $request       Request object
     * @return JsonResponse
     * @throws Exception
     */
    public function add(string $transactionId, Request $request): JsonResponse
    {
        Log::info('Begin translating the pumapay postback');

        try {
            $command = new AddBillerInteractionForJoinOnPumapayCommand(
                $transactionId,
                $request->input('payload', [])
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_OK);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (InvalidPayloadException | InvalidCommandException | InfrastructureException $e) {
            return $this->badRequest($e);
        } catch (Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
