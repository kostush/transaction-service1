<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use ProBillerNG\Logger\Log;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{

    //TODO: We should refactor this ...

    /**
     * @param \Throwable $error Error
     * @return \Illuminate\Http\JsonResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function notFound(\Throwable $error)
    {
        return $this->errorRequest($error, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param \Throwable $error Error
     * @return \Illuminate\Http\JsonResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function failedDependency(\Throwable $error)
    {
        return $this->errorRequest($error, Response::HTTP_FAILED_DEPENDENCY);
    }

    /**
     * @param \Throwable $error Error
     * @return \Illuminate\Http\JsonResponse
     */
    protected function badRequest(\Throwable $error)
    {
        return $this->errorRequest($error, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param \Throwable $error Error
     * @return \Illuminate\Http\JsonResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function internalServerError(\Throwable $error)
    {
        Log::logException($error);

        return response()->json(
            [
                'error' => $error->getPrevious() ? $error->getPrevious()->getMessage() : $error->getMessage(),
                'code'  => $error->getPrevious() ? $error->getPrevious()->getCode() : $error->getCode()
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * @param \Throwable $error Error
     * @param int        $code  Code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorRequest(\Throwable $error, int $code): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'error' => $error->getMessage(),
                'code'  => $error->getCode()
            ],
            $code
        );
    }
}
