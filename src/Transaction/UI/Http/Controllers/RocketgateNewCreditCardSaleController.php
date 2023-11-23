<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use Symfony\Component\HttpFoundation\Response;

class RocketgateNewCreditCardSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * RocketgateNewCreditCardSaleController constructor
     * @param PerformRocketgateNewCreditCardSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateNewCreditCardSaleCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * Create the transaction
     * @param Request $request The create Request
     * @return JsonResponse
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function create(Request $request)
    {
        Log::info('Begin transaction creation');

        // TODO: We should refactor the following DTO's like Rebill, Payment, etc as we're adding logic there when it
        // TODO: could be on the UI layer instead, so we avoid duplications as pointed here:
        // TODO: src/Transaction/Application/Services/Transaction/Rebill.php:62
        try {
            if ($request->has('payment.information.member')) {
                $userName = null;
                if ($request->has('payment.information.member.userName')) {
                    $userName = $request->input('payment.information.member.userName');
                }

                $member = new Member(
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
            } else {
                $member = null;
            }

            if ($request->has('rebill')) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start')
                );
            } else {
                $rebill = null;
            }

            $command = new PerformRocketgateNewCreditCardSaleCommand(
                (string) $request->input('siteId'),
                $request->input('amount'),
                (string) $request->input('currency'),
                new Payment(
                    (string) $request->input('payment.method'),
                    new NewCreditCardInformation(
                        (string) $request->input('payment.information.number'),
                        (string) $request->input('payment.information.expirationMonth'),
                        $request->input('payment.information.expirationYear'),
                        (string) $request->input('payment.information.cvv'),
                        $member
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
                    null,
                    (string) $request->input('billerFields.sharedSecret'),
                    $request->input('billerFields.simplified3DS'),
                ),
                $rebill,
                $request->input('useThreeD', false),
                (string) $request->input('returnUrl'),
                $request->input('isNSFSupported', false)
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);

        } catch (InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionCreationException | \Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
