<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Requests;

class RetrievePumapayQrCodeControllerRequest extends BaseRequest
{
    /**
     * TODO: It needs to add other fields
     * Validation Rules.
     *
     * @var array
     */
    protected $rules = [
        'transactionId' => 'filled|uuid',
    ];
}
