<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Validations;

class NetbillingUpdateRebillValidation extends ValidationBase
{

    /**
     * We should return an array of rules to be validated against provided Request
     * @return array
     */
    protected static function rules(): array
    {
        return [
            'transactionId'          => 'required|uuid',
            'siteTag'                => 'required|string',
            'accountId'              => 'required|string',
            'merchantPassword'       => 'required|string',
            'updateRebill'           => 'required|array',
            'updateRebill.amount'    => 'required_with:updateRebill|numeric',
            'updateRebill.frequency' => 'required_with:updateRebill|integer_only',
            'updateRebill.start'     => 'required_with:updateRebill|integer_only',
            'amount'                 => 'required|numeric',
            'payment'                => 'required|array',
            'payment.method'         => 'required|string',
            'payment.information'    => 'required|array',
            'payment.information.member'                 => 'required_without:payment.information.cardHash|array',
            'payment.information.member.firstName'       => 'required_with:payment.information.member|string',
            'payment.information.member.lastName'        => 'required_with:payment.information.member|string',
            'payment.information.member.userName'        => 'nullable|string',
            'payment.information.member.password'        => 'nullable|string',
            'payment.information.member.email'           => 'required_with:payment.information.member|string',
            'payment.information.member.phone'           => 'nullable|string',
            'payment.information.member.address'         => 'nullable|string',
            'payment.information.member.zipCode'         => 'required_with:payment.information.member|string',
            'payment.information.member.city'            => 'nullable|string',
            'payment.information.member.state'           => 'nullable|string',
            'payment.information.member.country'         => 'required_with:payment.information.member|string',
        ];
    }
}
