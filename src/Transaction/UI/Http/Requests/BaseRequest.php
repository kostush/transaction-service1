<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ProBillerNG\Transaction\Application\Exceptions\InvalidRequestException;

class BaseRequest extends Request
{
    /**
     * @var array
     */
    protected $messages = [
        'one_of'          => 'One of :others is required and cannot be empty',
        'is_int'          => 'The \':attribute\' with value :input has to be a valid integer',
        'is_float'        => 'The \':attribute\' with value :input has to be a valid float',
        'is_positive_int' => 'The \':attribute\' with value :input has to be a valid positive integer',
        'required'        => 'The \':attribute\' is required and cannot be empty',
        'string'          => 'The \':attribute\' with value :input has to be a string',
        'numeric'         => 'The \':attribute\' with value :input has to be numeric',
        'ip'              => 'The \':attribute\' with value :input is not a valid IP',
        'country_code'    => 'The \':attribute\' with value :input is not a valid country code',
        'payment_type'    => 'The \':attribute\' with value :input is not a valid payment type',
        'currency_code'   => 'The \':attribute\' with value :input is not a valid or expected currency code',
        'uuid'            => 'The \':attribute\' with value :input is not a valid UUID (RFC 4122)',
        'integer'         => 'The \':attribute\' with value :input is not an integer',
        'array'           => 'The \':attribute\' with value :input is not an array or JSON object',
        'required_with'   => 'The \':attribute\' is required with :others',
        'same'            => 'The \':attribute\' and \':other\' must match',
        'same_string'     => 'The \':attribute\' and \':other\' must match as string',
        'allowed_with'    => 'The \':attribute\' can only be set if \':others\' is (are) set',
        'digits'          => 'The length of the \':attribute\' with value :input has to be equals :value',
        'month'           => 'The \':attribute\' with value :input is not a valid month',
        'year'            => 'The \':attribute\' with value :input is not a valid year',
        'min'             => 'The \':attribute\' with value :input should be greater than or equals :min',
        'filled'          => 'The \':attribute\' must not be empty',
        'boolean_only'    => 'The \':attribute\' must be true or false',
    ];


    /**
     * ReactivateExpiredProcessRequest constructor.
     *
     * {@inheritdoc}
     *
     */
    public function __construct()
    {
        parent::__construct(
            app(Request::class)->query->all(),
            app(Request::class)->request->all(),
            app(Request::class)->attributes->all(),
            app(Request::class)->cookies->all(),
            app(Request::class)->files->all(),
            app(Request::class)->server->all(),
            app(Request::class)->content
        );

        $this->validate();
    }

    /**
     * Validates the request
     *
     * @return void
     * @throws InvalidRequestException
     */
    public function validate(): void
    {
        $validator = Validator::make(
            $this->json()->all(),
            $this->rules,
            $this->messages
        );
        if ($validator->fails()) {
            throw new InvalidRequestException($validator);
        }
    }
}
