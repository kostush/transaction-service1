<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use Illuminate\Http\Request;
use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingExistingCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingExistingCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use Symfony\Component\HttpFoundation\Response;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;

class NetbillingExistingCreditCardSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * NetbillingExistingCreditCardSaleController constructor
     * @param PerformNetbillingExistingCreditCardSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformNetbillingExistingCreditCardSaleCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request request
     * @return JsonResponse
     * @throws LoggerException
     */
    public function create(Request $request): JsonResponse
    {
        try {
            Log::info('Begin transaction creation for netbilling');
            $result = null;
            $rebill = null;

            if ($request->has('rebill')) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start')
                );
            }

            $command = new PerformNetbillingExistingCreditCardSaleCommand(
                (string) $request->input('siteId'),
                $request->input('amount'),
                (string) $request->input('currency'),
                new Payment(
                    (string) $request->input('payment.method'),
                    new ExistingCreditCardInformation(
                        (string) $request->input('payment.information.cardHash')
                    )
                ),
                $this->getBillerSettings($request),
                $rebill,
                new BillerLoginInfo($request->input('member.userName'), $request->input('member.password'))
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (NetbillingServiceException $e) {
            if (!empty($e->getPrevious())) {
                return $this->badRequest($e->getPrevious());
            }
            return $this->badRequest($e);
        } catch (\InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionCreationException | \Throwable $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * @param Request $request The request object
     * @return NetbillingChargeSettings
     * @throws InvalidPayloadException
     * @throws InvalidMerchantInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
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
                null
            );
        } else {
            throw new \InvalidArgumentException('Missing Biller information.');
        }

        return $billerSettings;
    }
}
