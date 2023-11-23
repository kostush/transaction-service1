<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Exceptions;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use ProBillerNG\Transaction\Code;

/**
 * Class InvalidRequestException
 * @package ProBillerNG\BundleManagement\Exceptions
 */
class InvalidRequestException extends ValidationException
{
    public $code = Code::INVALID_REQUEST_EXCEPTION;

    /**
     * InvalidRequestException constructor.
     *
     * @param Validator $validator Request validator
     */
    public function __construct(Validator $validator)
    {
        $response = new JsonResponse($validator->errors(), Response::HTTP_BAD_REQUEST);

        parent::__construct($validator, $response);
    }

    public function getStatusCode()
    {
        return $this->code;
    }

    public function messageBagConcatenated()
    {
        return implode(' ', [$this->getMessage(), implode('; ', $this->validator->getMessageBag()->all())]);
    }
}
