<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use ProBillerNG\Base\LumenMiddleware\Exceptions\InvalidXApiKeyAuthException;
use ProBillerNG\Base\LumenMiddleware\Exceptions\MissingPublicKeysConfigException;
use ProBillerNG\Transaction\Application\Exceptions\InvalidRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request   request
     * @param \Exception               $exception exception
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function render($request, Exception $exception)
    {
        if (env('APP_DEBUG')) {
            return parent::render($request, $exception);
        }

        if ($exception instanceof InvalidRequestException) {
            return response()->json(
                [
                    'code'  => $exception->getStatusCode(),
                    'error' => $exception->messageBagConcatenated(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($exception instanceof ApplicationException) {
            return response()->json(
                [
                    'status' => $exception->getStatusCode(),
                    'error'  => $exception->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json(
                [
                    'status' => $exception->getStatusCode(),
                    'error'  => 'Invalid Url - Page Not Found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(
                [
                    'status' => $exception->getStatusCode(),
                    'error'  => 'HTTP method not allowed'
                ],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        if ($exception instanceof InvalidXApiKeyAuthException ||
            $exception instanceof MissingPublicKeysConfigException
        ) {
            return response()->json(
                [
                    'status' => $exception->getStatusCode(),
                    'error'  => $exception->getMessage()
                ],
                $exception->getStatusCode()
            );
        }

        return response()->json(
            [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error'  => 'Internal server error'
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
