<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\TransactionLookupException;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\LookupThreeDsTwoCommandHandlerFactoryInterface;
use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Application\Services\Transaction\RocketgateLookupThreeDsTwoCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;

class LookupThreeDsTwoController extends Controller
{
    /**
     * @var RocketgateLookupThreeDsTwoCommandHandler
     */
    protected $handler;

    /**
     * LookupThreeDsTwoController constructor.
     * @param LookupThreeDsTwoCommandHandlerFactoryInterface $handler Command handler
     * @param Request                                        $request Request
     */
    public function __construct(
        LookupThreeDsTwoCommandHandlerFactoryInterface $handler,
        Request $request
    ) {
        $commandHandler = $handler->getHandler($request);
        $this->handler  = new TransactionalCommandHandler($commandHandler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('Begin 3ds2 lookup');

        try {
            $command = new LookupThreeDsTwoCommand(
                (string) $request->input('deviceFingerprintingId'),
                (string) $request->input('previousTransactionId'),
                (string) $request->input('redirectUrl'),
                new Payment(
                    (string) $request->input('payment.method'),
                    new NewCreditCardInformation(
                        (string) $request->input('payment.information.number'),
                        (string) $request->input('payment.information.expirationMonth'),
                        (string) $request->input('payment.information.expirationYear'),
                        (string) $request->input('payment.information.cvv'),
                        null
                    )
                ),
                $request->input('isNSFSupported', false)
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_OK);
        } catch (TransactionNotFoundException $e) {
            return $this->notFound($e);
        } catch (\InvalidArgumentException | InvalidPayloadException $e) {
            return $this->badRequest($e);
        } catch (TransactionLookupException | \Throwable $e) {
            return $this->internalServerError($e);
        }
    }
}
