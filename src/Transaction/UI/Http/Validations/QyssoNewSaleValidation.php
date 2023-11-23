<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Validations;


class QyssoNewSaleValidation extends ValidationBase
{
    protected static function rules(): array
    {
        return [
            'siteId'                               => 'required|uuid',
            'siteName'                             => 'required|string',
            'clientIp'                             => 'required|string',
            'amount'                               => 'required|numeric',
            'currency'                             => 'required|string',
            'tax'                                  => 'array',
            'payment'                              => 'required|array',
            'payment.type'                         => 'required|string',
            'payment.method'                       => 'nullable|string',
            'payment.information'                  => 'required|array',
            'payment.information.member'           => 'required|array',
            'payment.information.member.email'     => 'required|string',
            'payment.information.member.memberId'  => 'nullable|string',
            'payment.information.member.firstName' => 'nullable|string',
            'payment.information.member.lastName'  => 'nullable|string',
            'payment.information.member.userName'  => 'nullable|string',
            'payment.information.member.password'  => 'nullable|string',
            'payment.information.member.phone'     => 'nullable|string',
            'payment.information.member.address'   => 'nullable|string',
            'payment.information.member.zipCode'   => 'nullable|string',
            'payment.information.member.city'      => 'nullable|string',
            'payment.information.member.state'     => 'nullable|string',
            'payment.information.member.country'   => 'nullable|string',
            'rebill'                               => 'array',
            'rebill.amount'                        => 'required_with:rebill|numeric',
            'rebill.frequency'                     => 'required_with:rebill|integer',
            'rebill.start'                         => 'required_with:rebill|integer',
            'tax.initialAmount'                    => 'required_with:tax|array',
            'tax.initialAmount.beforeTaxes'        => 'required_with:tax.initialAmount|numeric',
            'tax.initialAmount.taxes'              => 'required_with:tax.initialAmount|numeric',
            'tax.initialAmount.afterTaxes'         => 'required_with:tax.initialAmount|numeric|same:amount',
            'tax.rebillAmount'                     => 'array',
            'tax.rebillAmount.beforeTaxes'         => 'required_with:tax.rebillAmount|numeric',
            'tax.rebillAmount.taxes'               => 'required_with:tax.rebillAmount|numeric',
            'tax.rebillAmount.afterTaxes'          => 'required_with:tax.rebillAmount|numeric|same:rebill.amount',
            'tax.taxApplicationId'                 => 'string',
            'tax.taxName'                          => 'string',
            'tax.taxRate'                          => 'numeric',
            'tax.custom'                           => 'string',
            'tax.taxType'                          => 'string',
            'billerFields'                         => 'required|array',
            'billerFields.companyNum'              => 'required|string',
            'billerFields.personalHashKey'         => 'required|string',
            'billerFields.notificationUrl'         => 'required|string',
            'billerFields.redirectUrl'             => 'required|string',
        ];
    }
}
