<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrieveQrCodeCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommandHandler;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\UI\Http\Requests\RetrievePumapayQrCodeControllerRequest;

class RetrievePumapayQrCodeController extends Controller
{
    /** @var TransactionalCommandHandler */
    protected $handler;

    /**
     * RetrievePumapayQrCodeController constructor.
     * @param RetrievePumapayQrCodeCommandHandler $handler Handler
     */
    public function __construct(RetrievePumapayQrCodeCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param RetrievePumapayQrCodeControllerRequest $request Request object
     * @return JsonResponse
     * @throws Exception
     */
    public function retrieve(RetrievePumapayQrCodeControllerRequest $request): JsonResponse
    {
        Log::info('Begin retrieval of the QR code');

        try {
            $rebill = null;

            if (!empty($request->input('rebill'))) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start', 0)
                );
            }

            $command = new RetrievePumapayQrCodeCommand(
                (string) $request->input('siteId', ''),
                (string) $request->input('currency', ''),
                (float) $request->input('amount', 0),
                $rebill,
                (string) $request->input('billerFields.businessId', ''),
                (string) $request->input('billerFields.businessModel', ''),
                (string) $request->input('billerFields.apiKey', ''),
                (string) $request->input('billerFields.title', ''),
                (string) $request->input('billerFields.description', ''),
                $request->input('transactionId')
            );

            /** @var RetrieveQrCodeCommandHttpDTO $result */
            $result = $this->handler->execute($command);

            return response()->json($result, $this->statusBasedOnRequest($command->transactionId()));
        } catch (PreviousTransactionNotFoundException |InvalidCommandException | InvalidPayloadException | MissingChargeInformationException | InvalidBillerResponseException $e) {
            Log::logException($e);
            return $this->badRequest($e);
        } catch (\Throwable $e) {
            Log::logException($e);
            return $this->internalServerError($e);
        }
    }

    /**
     * @param string|null $transactionId Transaction Id.
     * @return int
     */
    private function statusBasedOnRequest(?string $transactionId): int
    {
        if (isset($transactionId)) {
            return Response::HTTP_OK;
        }

        return Response::HTTP_CREATED;
    }
}
