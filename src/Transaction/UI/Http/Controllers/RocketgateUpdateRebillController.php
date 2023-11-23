<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RocketgateUpdateRebillController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $commandHandler;

    /**
     * @var array
     */
    protected $rebill;

    /**
     * RocketgateUpdateRebillController constructor.
     * @param RocketgateUpdateRebillCommandHandlerFactory $commandHandlerFactory CommandHandlerFactory
     * @param Request                                     $request               Request
     */
    public function __construct(
        RocketgateUpdateRebillCommandHandlerFactory $commandHandlerFactory,
        Request $request
    ) {
        $handler              = $commandHandlerFactory->getHandlerWithRebill($request)['handler'];
        $this->commandHandler = new TransactionalCommandHandler($handler);
        $this->rebill         = $commandHandlerFactory->getHandlerWithRebill($request)['rebill'];
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request)
    {
        Log::info('Begin update rebill transaction creation');

        try {
            $command = new PerformRocketgateUpdateRebillCommand(
                (string) $request->json('transactionId'),
                (string) $request->json('merchantId'),
                (string) $request->json('merchantPassword'),
                (string) $request->json('merchantCustomerId'),
                (string) $request->json('merchantInvoiceId'),
                (string) $request->json('merchantAccount'),
                $this->rebill,
                $request->json('amount'),
                (string) $request->json('currency'),
                $request->json('payment', [])

            );

            $result = $this->commandHandler->execute($command);

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
