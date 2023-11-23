<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Requests;

class RocketgateOtherPaymentTypeRequest extends BaseRequest
{
    /**
     * Validation Rules.
     *
     * @var array
     */
    protected $rules = [
        'siteId'                                  => 'required|uuid',
        'amount'                                  => 'required|numeric|filled|gt:0',
        'currency'                                => 'required|string',
        'rebill'                                  => 'array',
        'rebill.amount'                           => 'numeric|filled|gt:0',
        'rebill.start'                            => 'numeric|is_int|filled|gt:0',
        'rebill.frequency'                        => 'numeric|is_int|filled|gt:0',
        'payment.type'                            => 'required|string|filled',
        'payment.method'                          => 'required|string|filled',
        'payment.information'                     => 'array',
        'payment.information.routingNumber'       => 'required|numeric|string',
        'payment.information.accountNumber'       => 'required|numeric|string',
        'payment.information.savingAccount'       => 'required|bool',
        'payment.information.socialSecurityLast4' => 'required|numeric|string',
        'payment.information.member'              => 'array',
        'payment.information.member.firstName'    => 'required|string|filled',
        'payment.information.member.lastName'     => 'required|string|filled',
        'payment.information.member.userName'     => 'string|filled',
        'payment.information.member.password'     => 'string|filled',
        'payment.information.member.email'        => 'email|filled',
        'payment.information.member.phone'        => 'string|filled',
        'payment.information.member.address'      => 'required|string|filled',
        'payment.information.member.zipCode'      => 'required|string|filled',
        'payment.information.member.city'         => 'required|string|filled',
        'payment.information.member.state'        => 'required|string|filled',
        'payment.information.member.country'      => 'required|string|filled|max:2',
        'billerFields.merchantId'                 => 'required|numeric',
        'billerFields.merchantPassword'           => 'required|string',
        'billerFields.merchantSiteId'             => 'string|filled',
        'billerFields.merchantAccount'            => 'string|filled',
        'billerFields.merchantProductId'          => 'string|filled',
        'billerFields.merchantCustomerId'         => 'string|filled',
        'billerFields.merchantInvoiceId'          => 'string|filled',
        'billerFields.ipAddress'                  => 'ip|string|filled',
        'billerFields.sharedSecret'               => 'string',
        'billerFields.simplified3DS'              => 'bool',
    ];
}
