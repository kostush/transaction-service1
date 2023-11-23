<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;

class RocketgateErrorCodes
{
    const RG_CODE_SUCCESS            = 0;
    const RG_CODE_UNKNOWN_ERROR_CODE = 1;

    /**
     * Rocketgate Decline Reasons
     * Rocketgate error responses 100s
     */
    const RG_CODE_NO_MATCHING_TRANSACTION     = 100;
    const RG_CODE_CANNOT_VOID                 = 101;
    const RG_CODE_CANNOT_CREDIT               = 102;
    const RG_CODE_CANNOT_TICKET               = 103;
    const RG_CODE_DECLINED                    = 104;
    const RG_CODE_DECLINED_OVER_LIMIT         = 105;
    const RG_CODE_DECLINED_CVV2               = 106;
    const RG_CODE_DECLINED_EXPIRED_CARD       = 107;
    const RG_CODE_DECLINED_CALL               = 108;
    const RG_CODE_DECLINED_PICKUP_CARD        = 109;
    const RG_CODE_DECLINED_EXCESSIVE_USE      = 110;
    const RG_CODE_DECLINED_INVALID_CARD       = 111;
    const RG_CODE_DECLINED_INVALID_EXPIRATION = 112;
    const RG_CODE_DECLINED_BANK_UNAVAILABLE   = 113;
    const RG_CODE_DECLINED_AVS                = 117;
    const RG_CODE_DECLINED_USER_DECLINED      = 123;
    const RG_CODE_DECLINED_PIN                = 129;
    const RG_CODE_DECLINED_AVS_2              = 150;
    const RG_CODE_DECLINED_CVV2_2             = 151;
    const DECLINED_INVALID_TICKET             = 152;
    const RG_CODE_INTEGRATION_ERROR           = 154;
    const RG_CODE_DECLINED_CAVV               = 155;
    const RG_CODE_UNSUPPORTED_CARDTYPE        = 156;
    const RG_CODE_DECLINED_PROCESSOR_RISK     = 157;
    const RG_CODE_PREVIOUS_HARD_DECLINE       = 161;
    const RG_CODE_MERCH_ACCOUNT_LIMIT         = 162;
    const RG_CODE_DECLINED_CAVV_AUTOVOIDED    = 163;
    const RG_CODE_STOLEN_CARD                 = 164;


     /**
     * Rocketgate 3d secure responses
     * Rocketgate error responses 200s
     */
    const RG_CODE_DECLINED_SCRUB                    = 200;
    const RG_CODE_DECLINED_BLOCKED                  = 201;
    const RG_CODE_3DS_AUTH_REQUIRED                 = 202;
    const RG_CODE_3DS_NOT_ENROLLED                  = 203;
    const RG_CODE_3DS_INELIGIBLE                    = 204;
    const RG_CODE_3DS_REJECTED                      = 205;
    const RG_CODE_DUPLICATE_MEMBERSHIP_ID           = 208;
    const RG_CODE_DUPLICATE_MEMBERSHIP_CARD         = 209;
    const RG_CODE_DUPLICATE_MEMBERSHIP_EMAIL        = 210;
    const RG_CODE_DECLINED_EXCEEDED_MAX_AMOUNT      = 211;
    const RG_CODE_DECLINED_DUPLICATE                = 212;
    const RG_CODE_DECLINED_VELOCITY_CUSTOMER        = 213;
    const RG_CODE_DECLINED_VELOCITY_CARD_NUMBER     = 214;
    const RG_CODE_DECLINED_VELOCITY_EMAIL           = 215;
    const RG_CODE_IOVATION_DECLINE                  = 216;
    const RG_CODE_DECLINED_BREACHED_VELOCITY_LIMITS = 217;
    const RG_CODE_DUPLICATE_MEMBERSHIP_DEVICE       = 218;
    const RG_CODE_1CLICK_SOURCE                     = 219;
    const RG_CODE_TOO_MANY_CARDS                    = 220;
    const RG_CODE_AFFILIATE_BLOCKED                 = 221;
    const RG_CODE_TRIAL_ABUSE                       = 222;
    const RG_CODE_3DS_BYPASS                        = 223;
    const RG_CODE_NEW_CARD_NO_DEVICE                = 224;
    const RG_CODE_3DS2_INITIATION                   = 225;
    const RG_CODE_3DS_SCA_REQUIRED                  = 228;


    /**
     * Rocketgate error responses
     * Rocketgate error responses 300s
     */
    const RG_CODE_DECLINED_DNS_FAILURE                   = 300;
    const RG_CODE_DECLINED_UNABLE_TO_CONNECT             = 301;
    const RG_CODE_DECLINED_TRANSMIT_ERROR                = 302;
    const RG_CODE_DECLINED_READ_TIMEOUT                  = 303;
    const RG_CODE_DECLINED_READ_ERROR                    = 304;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE       = 305;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE1      = 306;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE2      = 307;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE3      = 308;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE4      = 309;
    const RG_CODE_DECLINED_APPLICATION_UNAVAILABLE5      = 310;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR     = 311;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_311 = 311;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_312 = 312;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_313 = 313;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_314 = 314;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_315 = 315;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_316 = 316;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_317 = 321;
    const RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_318 = 323;
    const RG_CODE_DECLINED_3D_SECURE_ERROR               = 325;
    const RG_CODE_USE_DIFFERENT_SERVER                   = 326;


    /**
     * Rocketgate Validation Errors
     * Rocketgate error responses 400s
     */
    const RG_CODE_XML_ERROR                    = 400;
    const RG_CODE_INVALID_CARD_NUMBER          = 403;
    const RG_CODE_INVALID_EXPIRATION           = 404;
    const RG_CODE_INVALID_AMOUNT               = 405;
    const RG_CODE_INVALID_MERCHANT_ID          = 406;
    const RG_CODE_INVALID_MERCHANT_ACCOUNT     = 407;
    const RG_CODE_INCOMPATIBLE_CARD_TYPE       = 408;
    const RG_CODE_NO_SUITABLE_ACCOUNT          = 409;
    const RG_CODE_INVALID_TRANSACT_ID          = 410;
    const RG_CODE_INVALID_ACCESS_CODE          = 411;
    const RG_CODE_INVALID_CUSTOMER_DATA_LENGTH = 412;
    const RG_CODE_INVALID_EXTERNAL_DATA_LENGTH = 413;
    const RG_CODE_INVALID_CUSTOMER_ID          = 414;
    const RG_CODE_INVALID_CURRENCY             = 418;
    const RG_CODE_INCOMPATIBLE_CURRENCY        = 419;
    const RG_CODE_INVALID_REBILL_ARGUMENTS     = 420;
    const RG_CODE_INVALID_PHONE                = 421;
    const RG_CODE_INVALID_COUNTRY_CODE         = 422;
    const RG_CODE_INCOMPATIBLE_DESCRIPTORS     = 436;
    const RG_CODE_INVALID_REFERRAL_DATA        = 437;
    const RG_CODE_INVALID_SITE_ID              = 438;
    const RG_CODE_INVOICE_ID_NOT_FOUND         = 441;
    const RG_CODE_MISSING_CUSTOMER_ID          = 443;
    const RG_CODE_MISSING_NAME                 = 444;
    const RG_CODE_MISSING_ADDRESS              = 445;
    const RG_CODE_MISSING_CVV                  = 446;
    const RG_CODE_MISSING_PARES                = 447;
    const RG_CODE_NO_ACTIVE_MEMBERSHIP         = 448;
    const RG_CODE_INVALID_CVV                  = 449;
    const RG_CODE_INVALID_3D_DATA              = 450;
    const RG_CODE_INVALID_CLONE_DATA           = 451;
    const RG_CODE_REDUNDANT_SUSPEND_RESUME     = 452;
    const RG_CODE_INVALID_PAYINFO_TRANSACT_ID  = 453;
    const RG_CODE_INVALID_CAPTURE_DAYS         = 454;
    const RG_CODE_SESSION_UNAVAILABLE          = 456;
    const RG_CODE_INVALID_TOKEN                = 457;


    protected static $messages = [
        self::RG_CODE_SUCCESS                     => 'Success',
        self::RG_CODE_UNKNOWN_ERROR_CODE          => 'Unknown error code',
        self::RG_CODE_NO_MATCHING_TRANSACTION     => 'A transaction referenced in a void, credit, or ticket operation cannot be found. This can occur if the TRANSACT_ID in the request is invalid or if the amount or card number do not match the original transaction.',
        self::RG_CODE_CANNOT_VOID                 => ' A void operation cannot be performed because the original transaction has already been voided credited or settled.',
        self::RG_CODE_CANNOT_CREDIT               => 'A credit operation cannot be performed because the original transaction has already been voided, credited, or has not been settled.',
        self::RG_CODE_CANNOT_TICKET               => 'A ticket operation cannot be performed because the original auth-only transaction has been voided or ticketed.',
        self::RG_CODE_DECLINED                    => 'The bank has declined the transaction.',
        self::RG_CODE_DECLINED_OVER_LIMIT         => 'The bank has declined the transaction because the account is over limit.',
        self::RG_CODE_DECLINED_CVV2               => 'The bank has declined the transaction because of a CVV2 mismatch.',
        self::RG_CODE_DECLINED_EXPIRED_CARD       => 'The bank has declined the transaction because the card is expired.',
        self::RG_CODE_DECLINED_CALL               => 'The bank has declined the transaction and has requested that the merchant call.',
        self::RG_CODE_DECLINED_PICKUP_CARD        => 'The bank has declined the transaction and has requested that the merchant pickup the card.',
        self::RG_CODE_DECLINED_EXCESSIVE_USE      => 'The bank has declined the transaction due to excessive use of the card.',
        self::RG_CODE_DECLINED_INVALID_CARD       => 'The bank has indicated that the account is invalid.',
        self::RG_CODE_DECLINED_INVALID_EXPIRATION => 'The bank has indicated that the account is expired.',
        self::RG_CODE_DECLINED_BANK_UNAVAILABLE   => 'The issuing bank is temporarily unavailable.  May be tried again later.',
        self::RG_CODE_DECLINED_AVS                => 'The transaction was declined due to an address verification mismatch.',
        self::RG_CODE_DECLINED_USER_DECLINED      => 'The Rebill transaction was declined because the user asked their bank to stop the rebill. It is suggested that merchants cancel subscriptions when they are managing their own rebilling.',
        self::RG_CODE_DECLINED_PIN                => 'Transaction was declined because of incorrect PIN or Pin tried exceeded',
        self::RG_CODE_DECLINED_AVS_2              => 'The transaction was declined due to an address verification mismatch.',
        self::RG_CODE_DECLINED_CVV2_2             => 'The bank has declined the transaction because of a CVV2 mismatch.',
        self::DECLINED_INVALID_TICKET             => 'A ticket request must be for less than or equal to the amount of the AUTH-ONLY.',
        self::RG_CODE_INTEGRATION_ERROR           => 'The transaction was rejected because it didn’t pass validation of supplied parameters',
        self::RG_CODE_DECLINED_CAVV               => 'Declined for 3DSecure Authentication',
        self::RG_CODE_UNSUPPORTED_CARDTYPE        => 'Transaction was declined because the bank returned an Unsupported CardType error.',
        self::RG_CODE_DECLINED_PROCESSOR_RISK     => 'Transaction was declined because the bank finds risky',
        self::RG_CODE_PREVIOUS_HARD_DECLINE       => 'Transaction was declined because of previous hard declines on the same card #',
        self::RG_CODE_MERCH_ACCOUNT_LIMIT         => 'Transaction was declined by acquiring bank for reaching account limit.',
        self::RG_CODE_DECLINED_CAVV_AUTOVOIDED    => 'Declined for 3DSecure Authentication',
        self::RG_CODE_STOLEN_CARD                 => 'The bank has declined the transaction because the card is stolen.',


        self::RG_CODE_DECLINED_SCRUB                        => 'Transaction was declined due to fraud scrubbing.',
        self::RG_CODE_DECLINED_BLOCKED                      => 'Transaction was declined due to the customer’s account being blocked.',
        self::RG_CODE_DUPLICATE_MEMBERSHIP_ID               => 'Transaction was declined because customer has a duplicate membership matching this customer id.',
        self::RG_CODE_DUPLICATE_MEMBERSHIP_CARD             => 'Transaction was declined because customer has a duplicate membership matching this card number.',
        self::RG_CODE_DUPLICATE_MEMBERSHIP_EMAIL            => 'Transaction was declined because customer has a duplicate membership matching this email.',
        self::RG_CODE_DECLINED_EXCEEDED_MAX_AMOUNT          => 'Transaction was declined because the amount exceeded the Max configured purchase amount.',
        self::RG_CODE_DECLINED_DUPLICATE                    => 'Transaction was declined because the card number and amount matched another transaction in configured time amount.',
        self::RG_CODE_DECLINED_VELOCITY_CUSTOMER            => 'Transaction was declined because customer has breached the velocity limits matched by Customer ID.',
        self::RG_CODE_DECLINED_VELOCITY_CARD_NUMBER         => 'Transaction was declined because customer has breached the velocity limits matched by card number.',
        self::RG_CODE_DECLINED_VELOCITY_EMAIL               => 'Transaction was declined because customer has breached the velocity limits matched by Email',
        self::RG_CODE_IOVATION_DECLINE                      => 'Transaction was declined because Iovation declined due to risk match.',
        self::RG_CODE_DECLINED_BREACHED_VELOCITY_LIMITS     => 'Transaction was declined because customer has breached the velocity limits matched by Iovation Device.',
        self::RG_CODE_DUPLICATE_MEMBERSHIP_DEVICE           => 'Transaction was declined because customer has a duplicate membership matching this Iovation Device.',
        self::RG_CODE_1CLICK_SOURCE                         => 'Transaction was declined by CheckAcctCompromised scrub for potential account compromise as ' .
                                                               'source IP or device was different from previous transactions.',
        self::RG_CODE_TOO_MANY_CARDS                        => 'Transaction was declined by NewCardScrubLimit scrub for a customer adding too many new cards ' .
                                                               'in a given period.',
        self::RG_CODE_AFFILIATE_BLOCKED                     => 'Transaction was declined due to the affiliate id black listing.',
        self::RG_CODE_TRIAL_ABUSE                           => 'Transaction was declined by LimitTrials scrub for a user signing up repeatedly for the same trial.',
        self::RG_CODE_NEW_CARD_NO_DEVICE                    => 'Transaction was declined by NewCardRequiresDevice scrub for a new cards with a missing ' .
                                                               'Iovation device ID.',


        self::RG_CODE_3DS_AUTH_REQUIRED => '3DSecure Authentication required',
        self::RG_CODE_3DS_NOT_ENROLLED  => 'The card is not enrolled in 3DSecure',
        self::RG_CODE_3DS_INELIGIBLE    => 'The card is not eligible for 3DSecure',
        self::RG_CODE_3DS_REJECTED      => '3DSecure Authentication rejected',
        self::RG_CODE_3DS_BYPASS        => '3DSecure bypass by merchant',
        self::RG_CODE_3DS2_INITIATION   => '3DSecure2 initiation',
        self::RG_CODE_3DS_SCA_REQUIRED  => '3DSecure requires SCA',

        self::RG_CODE_DECLINED_DNS_FAILURE                   => 'A DNS failure has prevented the merchant application from resolving RocketGate host names.',
        self::RG_CODE_DECLINED_UNABLE_TO_CONNECT             => 'The merchant application is unable to connect to an appropriate RocketGate host.',
        self::RG_CODE_DECLINED_TRANSMIT_ERROR                => 'An error occurred while transmitting data to the RocketGate servers.',
        self::RG_CODE_DECLINED_READ_TIMEOUT                  => 'A timeout occurred while waiting for a transaction response from the RocketGate servers.',
        self::RG_CODE_DECLINED_READ_ERROR                    => 'An error occurred while reading the response from the RocketGate servers.',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE       => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE1      => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE2      => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE3      => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE4      => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE5      => 'The transaction failed because applications within the RocketGate servers are unavailable or shutdown',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR     => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_311 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_312 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_313 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_314 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_315 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_316 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_317 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_318 => 'RocketGate was unable to complete the transaction with the processing institution.',
        self::RG_CODE_DECLINED_3D_SECURE_ERROR               => 'Internal error or setup issue with 3D Secure MPI communication.',
        self::RG_CODE_USE_DIFFERENT_SERVER                   => 'Data center maintenance.',


        self::RG_CODE_XML_ERROR                    => 'There was an error parsing the xml response from the gateway',
        self::RG_CODE_INVALID_CARD_NUMBER          => 'Card number is missing from request, does not pass MOD10 check, or contains an invalid bin.',
        self::RG_CODE_INVALID_EXPIRATION           => 'Expiration date is missing from request, or the month/year are invalid. Months must be 1 through 12.  Years must be 7 through 99 or 2007 through 2099.',
        self::RG_CODE_INVALID_AMOUNT               => 'Transaction amount is missing from request or is less than or equal to 0.',
        self::RG_CODE_INVALID_MERCHANT_ID          => 'Merchant ID is missing from request or is not a valid merchant ID.',
        self::RG_CODE_INVALID_MERCHANT_ACCOUNT     => 'Merchant account is missing from request or is not a valid account number for the specified merchant.',
        self::RG_CODE_INCOMPATIBLE_CARD_TYPE       => 'The merchant account specified in the request is not setup to accept the card type included in the request.',
        self::RG_CODE_NO_SUITABLE_ACCOUNT          => 'The optional account load balancing algorithm cannot find an available merchant account that accepts the card type specified in the transaction.',
        self::RG_CODE_INVALID_TRANSACT_ID          => 'The TRANSACT_ID is missing from the transaction or is not a valid transaction ID.',
        self::RG_CODE_INVALID_ACCESS_CODE          => 'The merchant access code specified in the request does not match the merchant’s access code.',
        self::RG_CODE_INVALID_CUSTOMER_DATA_LENGTH => 'Data provided for the customer’s billing address or IP address exceeds the maximum allowable field length.',
        self::RG_CODE_INVALID_EXTERNAL_DATA_LENGTH => 'Data provided for the merchant’s customer ID, invoice ID, UDF01, or UDF02 exceeds the maximum allowable field length.',
        self::RG_CODE_INVALID_CUSTOMER_ID          => 'Invalid or missing customer ID. Customer ID is required when modifying or canceling a subscription or when submitting transactions using the CARD_HASH parameter.',
        self::RG_CODE_INVALID_CURRENCY             => 'The currency code specified in the transaction request is invalid.',
        self::RG_CODE_INCOMPATIBLE_CURRENCY        => 'The currency code specified in the transaction request is not accepted by the merchant account specified in the request.',
        self::RG_CODE_INVALID_REBILL_ARGUMENTS     => 'Invalid data in one of the rebill parameters, or an invalid combination of the rebill parameters',
        self::RG_CODE_INVALID_PHONE                => 'The phone number submitted for a mobile billing operation is not valid.',
        self::RG_CODE_INVALID_COUNTRY_CODE         => 'The country code (COUNTRY_CODE) submitted is not valid.',
        self::RG_CODE_INCOMPATIBLE_DESCRIPTORS     => 'The account you are processing to doesn’t allow dynamic descriptors',
        self::RG_CODE_INVALID_REFERRAL_DATA        => 'This is returned when the REFERRING_MERCHANT_ID parameter does not reference a valid merchant, or if the “REFERRED_CUSTOMER_ID” parameter is missing or too long.',
        self::RG_CODE_INVALID_SITE_ID              => 'The MERCHANT_SITE_ID value is not a valid integer.',
        self::RG_CODE_INVOICE_ID_NOT_FOUND         => 'The MERCHANT_INVOICE_ID was not found.',
        self::RG_CODE_MISSING_CUSTOMER_ID          => 'The MERCHANT_CUSTOMER_ID field is required for this operation',
        self::RG_CODE_MISSING_NAME                 => 'The customer name is required for this operation. Please provide.',
        self::RG_CODE_MISSING_ADDRESS              => 'The address fields are required for this operation. Please provide',
        self::RG_CODE_MISSING_CVV                  => 'The CVV2 field is required for this operation. Please provide',
        self::RG_CODE_MISSING_PARES                => 'Missing the PARES value in a 3-D Secure transaction.',
        self::RG_CODE_NO_ACTIVE_MEMBERSHIP         => 'The membership record was found but not active.  For example, you’d hit this error when submitting a PerformRebillCancel() request and the account is already canceled.',
        self::RG_CODE_INVALID_CVV                  => 'CVV2_CHECK has been requested but the CVV was > 4 chars',
        self::RG_CODE_INVALID_3D_DATA              => 'Improper 3D Secure Data',
        self::RG_CODE_INVALID_CLONE_DATA           => 'Setting CLONE_CUSTOMER_RECORD, but omitting',
        self::RG_CODE_REDUNDANT_SUSPEND_RESUME     => 'PerformRebillUpdate() request attempted to suspend a subscription that is already suspended or to resume a subscription that is already active',
        self::RG_CODE_INVALID_PAYINFO_TRANSACT_ID  => 'The value provided for the PAYINFO_TRANSACT_ID is invalid.',
        self::RG_CODE_INVALID_CAPTURE_DAYS         => 'An invalid value was set in the CAPTURE_DAYS parameter',
        self::RG_CODE_SESSION_UNAVAILABLE          => 'HostedPageFields Ajax Session Unavailable',
        self::RG_CODE_INVALID_TOKEN                => 'HostedPageFields Ajax Invalid Session Token'
    ];

    protected static $errorCodesForAbortedStatus = [
        self::RG_CODE_DECLINED_DNS_FAILURE,
        self::RG_CODE_DECLINED_UNABLE_TO_CONNECT,
        self::RG_CODE_DECLINED_TRANSMIT_ERROR,
        self::RG_CODE_DECLINED_READ_TIMEOUT,
        self::RG_CODE_DECLINED_READ_ERROR,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE1,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE2,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE3,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE4,
        self::RG_CODE_DECLINED_APPLICATION_UNAVAILABLE5,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_311,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_312,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_313,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_314,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_315,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_316,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_317,
        self::RG_CODE_DECLINED_BANK_COMMUNICATIONS_ERROR_318,
        self::RG_CODE_DECLINED_3D_SECURE_ERROR,
        self::RG_CODE_USE_DIFFERENT_SERVER
    ];

    protected static $errorCodesForFailed3DStatus = [
        self::RG_CODE_3DS_BYPASS,
        self::RG_CODE_3DS_INELIGIBLE,
        self::RG_CODE_3DS_NOT_ENROLLED,
        self::RG_CODE_3DS_REJECTED
    ];

    /**
     * @param int $errorCode Error code
     *
     * @return mixed
     * @throws Exception
     */
    public static function getMessage(int $errorCode)
    {
        if (empty(self::$messages[$errorCode])) {
            Log::info('Received unknown error code from Rocketgate. Please investigate!', ['code' => $errorCode]);
            return self::$messages[self::RG_CODE_UNKNOWN_ERROR_CODE];
        }
        return self::$messages[$errorCode];
    }

    /**
     * @param int $errorCode Error code
     * @return bool
     */
    public static function isAbortedResponse(int $errorCode): bool
    {
        return in_array($errorCode, self::$errorCodesForAbortedStatus);
    }

    /**
     * @param int $errorCode Error code
     * @return bool
     */
    public static function isFailed3dsResponse(int $errorCode): bool
    {
        return in_array($errorCode, self::$errorCodesForFailed3DStatus);
    }

    /**
     * @param int $errorCode Error code
     * @return bool
     */
    public static function is3dsAuthRequired(int $errorCode): bool
    {
        return $errorCode === self::RG_CODE_3DS_AUTH_REQUIRED;
    }

    /**
     * @param int $errorCode Error code
     * @return bool
     */
    public static function is3ds2InitRequired(int $errorCode): bool
    {
        return $errorCode === self::RG_CODE_3DS2_INITIATION;
    }

    /**
     * @param   int $errorCode Error code
     * @return  bool
     */
    public static function is3dsScaRequired(int $errorCode): bool
    {
        return $errorCode === self::RG_CODE_3DS_SCA_REQUIRED;
    }
}
