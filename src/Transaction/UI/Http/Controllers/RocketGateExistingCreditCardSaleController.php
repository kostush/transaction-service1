<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketGateExistingCreditCardBillerFields;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateExistingCreditCardSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RocketGateExistingCreditCardSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * CreateTransactionController constructor
     * @param PerformRocketgateExistingCreditCardSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformRocketgateExistingCreditCardSaleCommandHandler $handler)
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
            if ($request->has('rebill')) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start')
                );
            } else {
                $rebill = null;
            }

            $command = new PerformRocketgateExistingCreditCardSaleCommand(
                (string) $request->input('siteId'),
                $request->input('amount'),
                (string) $request->input('currency'),
                new Payment(
                    (string) $request->input('payment.method'),
                    new ExistingCreditCardInformation(
                        (string) $request->input('payment.information.cardHash')
                    )
                ),
                new RocketGateExistingCreditCardBillerFields(
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
                $rebill,
                (bool) $request->input('useThreeD'),
                (string) $request->input('returnUrl'),
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionCreationException | Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
