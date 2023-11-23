<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionHealthQuery;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrieveTransactionHealthQueryHandler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionHealthCheckController
 * @package ProBillerNG\Transaction\UI\Http\Controllers
 */
class TransactionHealthCheckController extends Controller
{
    /**
     * @var RetrieveTransactionHealthQueryHandler
     */
    private $transactionHealthHandler;

    /**
     * TransactionHealthCheckController constructor.
     * @param RetrieveTransactionHealthQueryHandler $transactionHealthHandler Transaction Health Handler
     */
    public function __construct(RetrieveTransactionHealthQueryHandler $transactionHealthHandler)
    {
        $this->transactionHealthHandler = $transactionHealthHandler;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function retrieve()
    {
        try {
            $command = new RetrieveTransactionHealthQuery();

            $result = $this->transactionHealthHandler->execute($command);

            return response()->json($result, Response::HTTP_OK);

        } catch (\Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}