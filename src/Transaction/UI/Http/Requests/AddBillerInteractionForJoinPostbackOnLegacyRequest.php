<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Requests;

class AddBillerInteractionForJoinPostbackOnLegacyRequest extends BaseRequest
{
    /**
     * Validation Rules.
     *
     * @var array
     */
    protected $rules = [
        'type'                                         => 'required|string',
        'transactionId'                                => 'required|uuid',
        'statusCode'                                   => 'required|is_positive_int',
        'siteId'                                       => 'uuid|filled',
        'responsePayload'                              => 'required|array',
        'responsePayload.custom'                       => 'required_if:statusCode,==,0|array',
        'responsePayload.custom.mainProductId'         => 'required_if:statusCode,==,0|numeric',
        'responsePayload.data.productId'               => 'required_if:statusCode,==,0|numeric',
        'responsePayload.data.transactionId'           => 'required_if:statusCode,==,0|numeric',
        'responsePayload.data.memberDetails.member_id' => 'required_if:statusCode,==,0|numeric',
        'responsePayload.data.subscriptionId'          => 'required_if:statusCode,==,0|numeric',
        'responsePayload.data.allMemberSubscriptions'  => 'required_if:statusCode,==,0|array',
        'responsePayload.settleAmount'                 => 'required_if:statusCode,==,0|numeric',
    ];
}
