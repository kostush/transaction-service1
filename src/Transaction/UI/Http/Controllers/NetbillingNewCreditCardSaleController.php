<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use Illuminate\Http\Request;
use ProBillerNG\Transaction\Application\Services\ApplicationException;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use Symfony\Component\HttpFoundation\Response;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;

class NetbillingNewCreditCardSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * NetbillingNewCreditCardSaleController constructor
     *
     * @param PerformNetbillingNewCreditCardSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(
        PerformNetbillingNewCreditCardSaleCommandHandler $handler
    ) {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request request
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        try {
            Log::info('Begin transaction creation for netbilling');
            $result = null;

            if ($request->has('rebill')) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start')
                );
            } else {
                $rebill = null;
            }

            $command = new PerformNetbillingNewCreditCardSaleCommand(
                (string) $request->input('siteId'),
                $request->input('amount'),
                (string) $request->input('currency'),
                $this->getPaymentInfo($request),
                $this->getBillerSettings($request),
                $rebill
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (NetbillingServiceException $e) {
            if (!empty($e->getPrevious())) {
                return $this->badRequest($e->getPrevious());
            }

            return $this->badRequest($e);
        } catch (TransactionCreationException $e) {
            return $this->internalServerError($e);
        } catch (\InvalidArgumentException | InvalidPayloadException | ApplicationException $e) {
            return $this->badRequest($e);
        } catch (\Throwable $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * @param Request $request The request object
     *
     * @return NetbillingChargeSettings
     * @throws InvalidPayloadException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     */
    private function getBillerSettings(Request $request): NetbillingChargeSettings
    {
        $billerSettings = null;
        if ($request->has('billerFields')) {
            $billerSettings = new NetbillingChargeSettings(
                (string) $request->input('billerFields.siteTag'),
                (string) $request->input('billerFields.accountId'),
                (string) $request->input('billerFields.merchantPassword'),
                (int) $request->input('billerFields.initialDays'),
                (string) $request->input('billerFields.ipAddress'),
                (string) $request->input('billerFields.browser'),
                (string) $request->input('billerFields.host'),
                null, //todo make this field
                (string) $request->input('billerFields.binRouting'),
                (string) $request->input('billerFields.billerMemberId'),
                (boolean) $request->input('billerFields.disableFraudChecks')
            );
        } else {
            throw new \InvalidArgumentException('Missing Biller information.');
        }

        return $billerSettings;
    }

    /**
     * @param Request $request The request object
     *
     * @return Payment
     * @throws Exception
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    private function getPaymentInfo(Request $request): Payment
    {
        $payment = null;
        if ($request->has('payment')) {
            $member = null;

            if ($request->has('payment.information.member')) {
                $member = new Member(
                    (string) $request->input('payment.information.member.firstName'),
                    (string) $request->input('payment.information.member.lastName'),
                    (string) $request->input('payment.information.member.userName'),
                    (string) $request->input('payment.information.member.email'),
                    (string) $request->input('payment.information.member.phone'),
                    (string) $request->input('payment.information.member.address'),
                    (string) $request->input('payment.information.member.zipCode'),
                    (string) $request->input('payment.information.member.city'),
                    (string) $request->input('payment.information.member.state'),
                    (string) $request->input('payment.information.member.country'),
                    (string) $request->input('payment.information.member.password'),
                );
            } else {
                throw new \InvalidArgumentException('Missing member information.');
            }

            $payment = new Payment(
                (string) $request->input('payment.method'),
                new NewCreditCardInformation(
                    (string) $request->input('payment.information.number'),
                    (string) $request->input('payment.information.expirationMonth'),
                    $request->input('payment.information.expirationYear'),
                    (string) $request->input('payment.information.cvv'),
                    $member
                )
            );
        } else {
            throw new \InvalidArgumentException('Payment information is missing');
        }

        return $payment;
    }
}
