<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Requests;

class LegacyNewSaleRequest extends BaseRequest
{
    /**
     * Validation Rules.
     *
     * @var array
     */
    protected $rules = [
        'payment.type'                            => 'required|string|filled',
        'payment.method'                          => 'string|filled',
        'payment.information'                     => 'array',
        'payment.information.member'              => 'array',
        'payment.information.member.firstName'    => 'string|filled',
        'payment.information.member.lastName'     => 'string|filled',
        'payment.information.member.userName'     => 'string|filled',
        'payment.information.member.password'     => 'string|filled',
        'payment.information.member.email'        => 'email|filled',
        'payment.information.member.phone'        => 'string|filled',
        'payment.information.member.address'      => 'string|filled',
        'payment.information.member.zipCode'      => 'string|filled',
        'payment.information.member.city'         => 'string|filled',
        'payment.information.member.state'        => 'string|filled',
        'payment.information.member.country'      => 'string|filled|max:2',
        'charges'                                 => 'required|array',
        'charges.*.amount'                        => 'numeric|filled|min:0',
        'charges.*.productId'                     => 'required|is_int|min:0',
        'charges.*.currency'                      => 'required|string',
        'charges.*.siteId'                        => 'required|uuid',
        'charges.*.isMainPurchase'                => 'required|boolean_only',
        'charges.*.rebill'                        => 'array',
        'charges.*.rebill.amount'                 => 'required_with:charges.*.rebill|numeric|filled|min:0',
        'charges.*.rebill.frequency'              => 'required_with:charges.*.rebill|numeric|is_int|filled|min:0',
        'charges.*.rebill.start'                  => 'required_with:charges.*.rebill|numeric|is_int|filled|min:0',
        'charges.*.tax'                           => 'array',
        'charges.*.tax.taxApplicationId'          => 'uuid',
        'charges.*.tax.taxName'                   => 'string',
        'charges.*.tax.taxRate'                   => 'numeric|filled|min:0',
        'charges.*.tax.displayChargedAmount'      => 'filled|boolean_only',
        'charges.*.tax.taxType'                   => 'string',
        'charges.*.tax.initialAmount'             => 'required_with:charges.*.tax|array',
        'charges.*.tax.initialAmount.beforeTaxes' => 'required_with:charges.*.tax.initialAmount|numeric|filled|min:0',
        'charges.*.tax.initialAmount.taxes'       => 'required_with:charges.*.tax.initialAmount|numeric|filled|min:0',
        'charges.*.tax.initialAmount.afterTaxes'  => 'required_with:charges.*.tax.initialAmount|numeric|filled|min:0',
        'charges.*.tax.rebillAmount'              => 'array',
        'charges.*.tax.rebillAmount.beforeTaxes'  => 'required_with:charges.*.tax.rebillAmount|numeric|filled|min:0',
        'charges.*.tax.rebillAmount.taxes'        => 'required_with:charges.*.tax.rebillAmount|numeric|filled|min:0',
        'charges.*.tax.rebillAmount.afterTaxes'   => 'required_with:charges.*.tax.rebillAmount|numeric|filled|min:0',
        'billerFields.legacyMemberId'             => 'numeric',
        'billerFields.returnUrl'                  => 'required|string|url',
        'billerFields.postbackUrl'                => 'required|string|url',
        'billerFields.others'                     => 'array',
    ];
}
