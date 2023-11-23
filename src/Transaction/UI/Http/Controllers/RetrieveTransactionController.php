<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\ResponseFactory;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use Illuminate\Http\Response;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionQuery;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionQueryHandler;
use ProBillerNG\Transaction\Domain\DomainException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\RetrieveTransactionException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\UnknownBillerNameException;

class RetrieveTransactionController extends Controller
{
    /**
     * @var RetrieveTransactionQueryHandler
     */
    protected $handler;

    /**
     * CreateTransactionController constructor
     *
     * @param RetrieveTransactionQueryHandler $handler Non-transactional Command Handler
     */
    public function __construct(RetrieveTransactionQueryHandler $handler)
    {
        $this->handler = $handler;
    }


    /**
     * @param string $transactionId Transaction Id
     * @return JsonResponse|Response|ResponseFactory
     * @throws LoggerException
     */
    public function retrieve(string $transactionId)
    {
        try {
            Log::info('Begin transaction retrieval process', ['transactionId' => $transactionId]);

            $command = new RetrieveTransactionQuery(
                $transactionId
            );
            $result  = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_OK);

        } catch (InvalidPayloadException | UnknownBillerNameException $e) {
            return $this->badRequest($e);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (RetrieveTransactionException $e) {
            return $this->badRequest($e);
        } catch (\Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
