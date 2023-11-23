<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction;

class Code
{
    // 1000-1099 Transaction generic exceptions
    const TRANSACTION_EXCEPTION               = 1000;
    const COULD_NOT_CREATE_TRANSACTION        = 1001;
    const TRANSACTION_NOT_FOUND               = 1003;
    const RETRIEVE_TRANSACTION_EXCEPTION      = 1004;
    const RETRIEVE_QR_CODE_EXCEPTION          = 1005;
    const PREVIOUS_TRANSACTION_NOT_FOUND      = 1006;
    const CHARGE_SETTINGS_NOT_FOUND_EXCEPTION = 1007;
    const COULD_NOT_PERFORM_LOOKUP            = 1008;

    // 3000-3999 Transaction exceptions
    const ILLEGAL_STATE_TRANSITION_EXCEPTION              = 3010;
    const INVALID_STATUS_NAME                             = 3011;
    const BILLER_INTERACTION_PAYLOAD_INVALID              = 3020;
    const BILLER_INTERACTION_TYPE_INVALID                 = 3030;
    const TRANSACTION_ALREADY_PROCESSED                   = 3032;
    const PUMAPAY_PREVIOUS_TRANSACTION_SHOULD_BE_APPROVED = 3033;
    const INVALID_TRANSACTION_TYPE_EXCEPTION              = 3034;
    const TRANSACTION_HAS_NO_BILLER_INTERACTIONS          = 3035;
    const REBILL_NOT_SET_EXCEPTION                        = 3036;
    const INVALID_STATUS_EXCEPTION                        = 3037;
    const INVALID_BILLER_RECEIVED                         = 3038;
    const INVALID_THREEDS_VERSION                         = 3039;
    const INVALID_BILLER_NAME_RECEIVED                    = 3040;
    const INVALID_PREVIOUS_TRANSACTION                    = 3041;
    const PREVIOUS_TRANSACTION_CORRUPTED_DATA             = 3042;

    // 4000 - 4999 Event exceptions
    const AGGREGATE_ID_MISSING = 4000;

    // 5000-5999 Translation exceptions
    const UNHANDLED_PAYMENT_TYPE_FOR_BILLER_EXCEPTION   = 5010;
    const UNHANDLED_PAYMENT_METHOD_FOR_BILLER_EXCEPTION = 5011;
    const INVALID_BILLER_RESPONSE_EXCEPTION             = 5020;
    const INVALID_PAYMENT_INFORMATION_EXCEPTION         = 5021;

    // 6000-6999 Biller Services Exception
    const ROCKETGATE_SERVICE_EXCEPTION = 6010;

    const PUMAPAY_SERVICE_EXCEPTION         = 6100;
    const NETBILLING_SERVICE_EXCEPTION      = 6200;
    const LEGACY_SERVICE_RESPONSE_EXCEPTION = 6300;

    // 7000 - 7999 Invalid command errors
    const INVALID_COMMAND_GIVEN         = 7000;
    const INVALID_QUERY_GIVEN           = 7001;
    const BILLER_OBFUSCATOR_NOT_DEFINED = 7002;

    // 8000 - 8999 Circuit Breaker
    const ROCKETGATE_CIRCUIT_BREAKER_OPEN = 8000;
    const NETBILLING_CIRCUIT_BREAKER_OPEN = 8001;
    const EPOCH_CIRCUIT_BREAKER_OPEN      = 8002;
    const LEGACY_CIRCUIT_BREAKER_OPEN     = 8003;

    // 20000-20099 Missing exceptions
    const MISSING_CREDIT_CARD_INFORMATION_EXCEPTION         = 20000;
    const INVALID_CREDIT_CARD_INFORMATION_EXCEPTION         = 20001;
    const MISSING_CHARGE_INFORMATION_EXCEPTION              = 20002;
    const INVALID_CHARGE_INFORMATION_EXCEPTION              = 20003;
    const MISSING_MERCHANT_INFORMATION_EXCEPTION            = 20004;
    const INVALID_MERCHANT_INFORMATION_EXCEPTION            = 20005;
    const MISSING_TRANSACTION_INFORMATION_EXCEPTION         = 20006;
    const INVALID_TRANSACTION_INFORMATION_EXCEPTION         = 20007;
    const MISSING_INITIAL_DAYS_EXCEPTION                    = 20008;
    const MAIN_PURCHASE_NOT_FOUND_EXCEPTION                 = 20009;
    const NOT_ALLOWED_MORE_THAN_ONE_MAIN_PURCHASE_EXCEPTION = 20010;
    const INVALID_PAYMENT_TYPE_EXCEPTION                    = 20011;
    const AFTER_TAX_DOES_NOT_MATCH_WITH_AMOUNT              = 20012;
    const MISSING_SITE_ID_FOR_CROSS_SALE_EXCEPTION          = 20013;
    const INVALID_PAYMENT_METHOD_EXCEPTION                  = 20014;
    const INVALID_INITIAL_DAYS_EXCEPTION                    = 20015;

    //Application errors
    const APPLICATION_EXCEPTION_INVALID_SESSION_ID = 5555;
    const INVALID_REQUEST_EXCEPTION                = 5003;
    const UNKNOWN_BILLER_NAME_EXCEPTION            = 5004;

    const FIRESTORE_EXCEPTION = 9000;

    protected static $messages = [
        // 1000-1099 Transaction generic exceptions
        self::TRANSACTION_EXCEPTION                           => 'Transaction processing exception',
        self::COULD_NOT_CREATE_TRANSACTION                    => 'Transaction could not be created',
        self::TRANSACTION_NOT_FOUND                           => 'Transaction not found',
        self::RETRIEVE_TRANSACTION_EXCEPTION                  => 'Could not retrieve transaction',
        self::RETRIEVE_QR_CODE_EXCEPTION                      => 'Could not retrieve QR code. Reason: %s',
        self::PREVIOUS_TRANSACTION_NOT_FOUND                  => 'Previous transaction not found. Transaction id: %s',
        self::CHARGE_SETTINGS_NOT_FOUND_EXCEPTION             => 'Charge Settings not found',
        self::COULD_NOT_PERFORM_LOOKUP                        => 'Transaction lookup could not be performed',

        // 3000-3999 transaction exceptions
        self::ILLEGAL_STATE_TRANSITION_EXCEPTION              => 'Illegal transaction state change',
        self::INVALID_STATUS_NAME                             => 'Invalid status name',
        self::BILLER_INTERACTION_PAYLOAD_INVALID              => 'Invalid biller interaction payload json',
        self::BILLER_INTERACTION_TYPE_INVALID                 => 'Invalid biller interaction type %s',
        self::INVALID_BILLER_RECEIVED                         => 'This transaction was not performed using %s',
        self::TRANSACTION_ALREADY_PROCESSED                   => 'Transaction with id %s was already processed',
        self::PUMAPAY_PREVIOUS_TRANSACTION_SHOULD_BE_APPROVED => 'Transaction with id %s should be approved',
        self::INVALID_TRANSACTION_TYPE_EXCEPTION              => 'Transaction type should be charge',
        self::TRANSACTION_HAS_NO_BILLER_INTERACTIONS          => 'No biller interactions for the transaction: %s',
        self::REBILL_NOT_SET_EXCEPTION                        => 'Transaction: %s does not support rebill',
        self::INVALID_STATUS_EXCEPTION                        => 'Transaction has an invalid status',
        self::INVALID_THREEDS_VERSION                         => 'Transaction has an invalid threeds version',
        self::INVALID_BILLER_NAME_RECEIVED                    => 'The transaction was performed with %s instead of %s',
        self::INVALID_PREVIOUS_TRANSACTION                    => 'Previous transaction should be pending, \'%s\' given.',
        self::PREVIOUS_TRANSACTION_CORRUPTED_DATA             => 'Previous transaction is missing \'%s\' field.',

        // 4000 - 4999 Event exceptions
        self::AGGREGATE_ID_MISSING                            => 'Aggregate Id not set on Event: %s',

        // 5000-5999 Translation exceptions
        self::UNHANDLED_PAYMENT_TYPE_FOR_BILLER_EXCEPTION     => 'Payment type \'%s\' is not supported ' .
                                                                 'by biller with id \'%s\'',
        self::UNHANDLED_PAYMENT_METHOD_FOR_BILLER_EXCEPTION   => 'Payment method \'%s\' is not supported ' .
                                                                 'by biller with id \'%s\'',
        self::INVALID_BILLER_RESPONSE_EXCEPTION               => 'Invalid response from biller',
        self::INVALID_PAYMENT_INFORMATION_EXCEPTION           => 'Invalid payment information',

        // 6000-6999 Biller Services Exception
        self::ROCKETGATE_SERVICE_EXCEPTION                    => 'Rocketgate Service Exception',

        self::PUMAPAY_SERVICE_EXCEPTION         => 'Pumapay Service Exception: %s',
        self::NETBILLING_SERVICE_EXCEPTION      => 'Netbilling Service Exception',
        self::LEGACY_SERVICE_RESPONSE_EXCEPTION => 'Legacy Service Exception',

        // 7000 - 7999 Invalid command errors
        self::INVALID_COMMAND_GIVEN             => 'Invalid command given. Expecting: \'%s\', Got: \'%s\'',
        self::INVALID_QUERY_GIVEN               => 'Invalid query given. Expecting: \'%s\', Got: \'%s\'',
        self::BILLER_OBFUSCATOR_NOT_DEFINED     => 'No obfuscator defined for biller: \'%s\'',

        // 8000 - 8999 Circuit Breaker
        self::ROCKETGATE_CIRCUIT_BREAKER_OPEN   => 'Cannot contact Rocketgate Service. ' .
                                                   'Circuit breaker open.',

        self::NETBILLING_CIRCUIT_BREAKER_OPEN => 'Cannot contact Netbilling Service. ' .
                                                 'Circuit breaker open.',

        self::EPOCH_CIRCUIT_BREAKER_OPEN                        => 'Cannot contact Epoch Service. ' .
                                                                   'Circuit breaker open.',

        // 20000-20099 Missing exceptions
        self::MISSING_CREDIT_CARD_INFORMATION_EXCEPTION         => 'Missing credit card information field: %s',
        self::INVALID_CREDIT_CARD_INFORMATION_EXCEPTION         => 'Invalid credit card information field: %s',
        self::MISSING_CHARGE_INFORMATION_EXCEPTION              => 'Missing charge information field: %s',
        self::INVALID_CHARGE_INFORMATION_EXCEPTION              => 'Invalid charge information field: %s',
        self::MISSING_MERCHANT_INFORMATION_EXCEPTION            => 'Missing merchant information field: %s',
        self::INVALID_MERCHANT_INFORMATION_EXCEPTION            => 'Invalid merchant information field: %s',
        self::MISSING_TRANSACTION_INFORMATION_EXCEPTION         => 'Missing transaction information field: %s',
        self::INVALID_TRANSACTION_INFORMATION_EXCEPTION         => 'Invalid transaction information field: %s',
        self::MISSING_INITIAL_DAYS_EXCEPTION                    => 'Missing initial days field: %s',
        self::MAIN_PURCHASE_NOT_FOUND_EXCEPTION                 => 'Main Purchase not found.',
        self::NOT_ALLOWED_MORE_THAN_ONE_MAIN_PURCHASE_EXCEPTION => 'Not allowed more than one main purchase.',
        self::INVALID_PAYMENT_TYPE_EXCEPTION                    => 'Invalid payment type: %s',
        self::AFTER_TAX_DOES_NOT_MATCH_WITH_AMOUNT              => 'The given data was invalid. The %s and %s must match.',
        self::MISSING_SITE_ID_FOR_CROSS_SALE_EXCEPTION          => 'Missing siteId to create cross sale transaction',
        self::INVALID_PAYMENT_METHOD_EXCEPTION                  => 'Invalid payment method: %s',
        self::INVALID_INITIAL_DAYS_EXCEPTION                    => 'If initial days is 0 rebill information should not be present',

        //Application errors
        self::APPLICATION_EXCEPTION_INVALID_SESSION_ID          => 'Invalid session id!',
        self::UNKNOWN_BILLER_NAME_EXCEPTION                     => 'Unknown biller Id: %s, You have passed on your transaction creation.',

        self::FIRESTORE_EXCEPTION => 'Firestore connection error!',
    ];

    /**
     * @param int $errorCode Error code
     * @return mixed
     */
    public static function getMessage(int $errorCode)
    {
        return self::$messages[$errorCode] ?? self::$messages[self::TRANSACTION_EXCEPTION];
    }
}
