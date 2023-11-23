<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateOtherPaymentTypeSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateOtherPaymentTypeSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\UI\Http\Requests\RocketgateOtherPaymentTypeRequest;
use Symfony\Component\HttpFoundation\Response;

class RocketgateOtherPaymentTypeSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * RocketgateOtherPaymentTypeSaleController constructor
     * @param PerformRocketgateOtherPaymentTypeSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateOtherPaymentTypeSaleCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * Create the transaction
     * @param RocketgateOtherPaymentTypeRequest $request The create Request
     * @throws Exception
     * @throws \InvalidArgumentException
     * @return JsonResponse
     */
    public function create(RocketgateOtherPaymentTypeRequest $request)
    {

        try {
            $command = PerformRocketgateOtherPaymentTypeSaleCommand::createFromRequest($request);

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionCreationException | \Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
