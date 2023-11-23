<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\PreviousTransactionCorruptedDataException;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionCreationException;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingUpdateRebillCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\NetbillingUpdateRebillCommandHandlerFactory;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use Illuminate\Http\Request;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\NetbillingServiceException;
use ProBillerNG\Transaction\UI\Http\Validations\NetbillingUpdateRebillValidation;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class NetbillingUpdateRebillController
 * @package ProBillerNG\Transaction\UI\Http\Controllers
 */
class NetbillingUpdateRebillController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $commandHandler;

    /**
     * @var array
     */
    protected $rebill;

    /**
     * NetbillingUpdateRebillController constructor.
     * @param NetbillingUpdateRebillCommandHandlerFactory $commandHandlerFactory Command Factory
     * @param Request                                     $request               Request Payload
     */
    public function __construct(
        NetbillingUpdateRebillCommandHandlerFactory $commandHandlerFactory,
        Request $request
    ) {
        $handlerWithRebill    = $commandHandlerFactory->getHandlerWithRebill($request);
        $this->commandHandler = new TransactionalCommandHandler($handlerWithRebill['handler']);
        $this->rebill         = $handlerWithRebill['rebill'];
    }

    /**
     * @param Request $request request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request)
    {
        Log::info('Begin update netbilling rebill transaction creation');

        try {
            NetbillingUpdateRebillValidation::validate($request);

            $command = new PerformNetbillingUpdateRebillCommand(
                (string) $request->json('transactionId'),
                (string) $request->json('siteTag'),
                (string) $request->json('accountId'),
                (string) $request->json('merchantPassword'),
                $this->rebill,
                $request->json('amount'),
                $this->getPaymentInfo($request),
                (string) $request->json('binRouting'),
                null // no currency needed for netbilling. Account tied to currency
            );

            $result = $this->commandHandler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (NetbillingServiceException $e) {
            if (!empty($e->getPrevious())) {
                return $this->badRequest($e->getPrevious());
            }

            return $this->badRequest($e);
        } catch (InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (PreviousTransactionCorruptedDataException $e) {
            return $this->failedDependency($e);
        } catch (TransactionCreationException | Throwable $e) {
            return $this->internalServerError($e);
        }
    }

    /**
     * @param Request $request The request object
     * @return Payment
     * @throws Exception
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws InvalidCreditCardExpirationDateException
     */
    public function getPaymentInfo(Request $request): Payment
    {
        if ($request->has('payment')) {
            if ($request->has('payment.information.cardHash')) {
                return new Payment(
                    (string) $request->input('payment.method'),
                    new ExistingCreditCardInformation(
                        (string) $request->input('payment.information.cardHash')
                    )
                );
            }

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
                throw new InvalidArgumentException('Missing member information.');
            }

            return new Payment(
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
            throw new InvalidArgumentException('Payment information is missing');
        }
    }
}
