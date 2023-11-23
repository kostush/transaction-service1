<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingCancelRebillCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NetbillingCancelRebillController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * CreateTransactionController constructor
     * @param PerformNetbillingCancelRebillCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformNetbillingCancelRebillCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request)
    {
        Log::info('Begin cancel rebill transaction creation');

        try {
            $command = new PerformNetbillingCancelRebillCommand(
                (string) $request->json('transactionId'),
                (string) $request->json('siteTag'),
                (string) $request->json('accountId'),
                (string) $request->json('merchantPassword'),
            );

            $result = $this->handler->execute($command);

            if ($result->shouldReturn400()) {
                return response()->json($result, Response::HTTP_BAD_REQUEST);
            }

            return response()->json($result, Response::HTTP_CREATED);

        } catch (NetbillingServiceException $e) {
            if (!empty($e->getPrevious())) {
                return $this->badRequest($e->getPrevious());
            }

            return $this->badRequest($e);
        } catch (InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (TransactionCreationException | Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}