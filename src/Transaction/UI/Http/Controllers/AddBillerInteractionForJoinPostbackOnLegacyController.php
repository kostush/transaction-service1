<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\AddBillerInteractionForJoinOnLegacyCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\AddBillerInteractionForJoinOnLegacyCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\UI\Http\Requests\AddBillerInteractionForJoinPostbackOnLegacyRequest;

class AddBillerInteractionForJoinPostbackOnLegacyController extends Controller
{
    /**
     * @var AddBillerInteractionForJoinOnLegacyCommandHandler
     */
    protected $handler;

    /**
     * @param AddBillerInteractionForJoinOnLegacyCommandHandler $handler Command Handler
     */
    public function __construct(AddBillerInteractionForJoinOnLegacyCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param AddBillerInteractionForJoinPostbackOnLegacyRequest $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(AddBillerInteractionForJoinPostbackOnLegacyRequest $request): JsonResponse
    {
        Log::info('Begin Legacy biller interaction creation');

        try {
            $command = new AddBillerInteractionForJoinOnLegacyCommand(
                $request->input('transactionId'),
                $request->input('responsePayload'),
                (int) $request->input('statusCode'),
                $request->input('type'),
                $request->input('siteId')
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_OK);
        } catch (InvalidCommandException | InvalidPayloadException | InvalidBillerResponseException $e) {
            Log::logException($e);
            return $this->badRequest($e);
        } catch (TransactionNotFoundException  $e) {
            Log::logException($e);
            return $this->notFound($e);
        } catch (\Throwable $e) {
            Log::logException($e);
            return $this->internalServerError($e);
        }
    }
}
