<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PumapayCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\InfrastructureException;
use Throwable;

class PumapayCancelRebillController extends Controller
{
    /** @var TransactionalCommandHandler */
    protected $handler;

    /**
     * PumapayCancelRebillController constructor.
     * @param PumapayCancelRebillCommandHandler $handler PumapayCancelRebillCommandHandler
     */
    public function __construct(PumapayCancelRebillCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request object
     * @return JsonResponse
     * @throws Exception
     */
    public function cancel(Request $request): JsonResponse
    {
        Log::info('Begin Cancel Pumapay Rebill');

        try {
            $command = new PumapayCancelRebillCommand(
                (string) $request->input('transactionId', ''),
                (string) $request->input('billerFields.businessId', ''),
                (string) $request->input('billerFields.businessModel', ''),
                (string) $request->input('billerFields.apiKey', '')
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (InvalidPayloadException | InvalidCommandException | InfrastructureException $e) {
            return $this->badRequest($e);
        } catch (Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
