<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use Illuminate\Http\Request;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;

class PerformRocketgateOtherPaymentTypeSaleCommand extends PerformRocketgateSaleCommand
{
    /**
     * @param Request $request Request
     * @return static
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidPaymentMethodException
     */
    public static function createFromRequest(Request $request): self
    {
        return new static(
            (string) $request->input('siteId'),
            $request->input('amount'),
            (string) $request->input('currency'),
            new Payment(
                (string) $request->input('payment.type'),
                new OtherPaymentTypeInformation(
                    (string) $request->input('payment.information.routingNumber'),
                    (string) $request->input('payment.information.accountNumber'),
                    (bool) $request->input('payment.information.savingAccount'),
                    (string) $request->input('payment.information.socialSecurityLast4'),
                    self::member($request),
                    (string) $request->input('payment.method')
                )
            ),
            RocketGateChargeSettings::create(
                (string) $request->input('billerFields.merchantId'),
                (string) $request->input('billerFields.merchantPassword'),
                (string) $request->input('billerFields.merchantCustomerId'),
                (string) $request->input('billerFields.merchantInvoiceId'),
                (string) $request->input('billerFields.merchantAccount'),
                (string) $request->input('billerFields.merchantSiteId'),
                (string) $request->input('billerFields.merchantProductId'),
                (string) $request->input('billerFields.merchantDescriptor'),
                (string) $request->input('billerFields.ipAddress'),
                (string) $request->input('billerFields.referringMerchantId'),
                (string) $request->input('billerFields.sharedSecret'),
                $request->input('billerFields.simplified3DS')
            ),
            self::returnRebill($request)
        );
    }

    /**
     * @param Request $request Request
     * @return Member|null
     */
    private static function member(Request $request): ?Member
    {
        if ($request->has('payment.information.member')) {
            $userName = null;
            if ($request->has('payment.information.member.userName')) {
                $userName = $request->input('payment.information.member.userName');
            }

            return new Member(
                (string) $request->input('payment.information.member.firstName'),
                (string) $request->input('payment.information.member.lastName'),
                (string) $userName,
                (string) $request->input('payment.information.member.email'),
                (string) $request->input('payment.information.member.phone'),
                (string) $request->input('payment.information.member.address'),
                (string) $request->input('payment.information.member.zipCode'),
                (string) $request->input('payment.information.member.city'),
                (string) $request->input('payment.information.member.state'),
                (string) $request->input('payment.information.member.country')
            );
        }

        return null;
    }

    /**
     * @param Request $request Request
     * @return Rebill|null
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    private static function returnRebill(Request $request): ?Rebill
    {
        if ($request->has('rebill')) {
            return new Rebill(
                $request->input('rebill.amount'),
                $request->input('rebill.frequency'),
                $request->input('rebill.start')
            );
        }
        return null;
    }
}
