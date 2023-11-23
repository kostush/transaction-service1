<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;

abstract class ValidationBase
{
    /** @var array */
    private static $messages = [
        'uuid' => 'The :attribute with value :input is not valid uuid'
    ];

    /**
     * We should return an array of rules to be validated against provided Request
     * @return array
     */
    abstract protected static function rules(): array;

    /**
     * @param Request $request Request The Request
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function validate(Request $request): void
    {
        $routeParams = !empty($request->route()[2]) ? $request->route()[2] : [];

        $data = array_merge(
            $routeParams,
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );

        $validator = Validator::make($data, static::rules(), self::getMessages());

        if ($validator->fails()) {
            throw new InvalidChargeInformationException(
                self::formatErrorMessages($validator->errors()->all())
            );
        }
    }

    /**
     * @return array
     */
    protected static function getMessages(): array
    {
        return self::$messages;
    }

    /**
     * @return string
     */
    protected static function formatErrorMessages(array $validatorErrors): string
    {
        return '[' . implode('], [', array_values($validatorErrors)) . ']';
    }
}
