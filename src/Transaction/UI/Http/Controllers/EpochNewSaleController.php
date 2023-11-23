<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformEpochNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformEpochNewSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\EpochBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\InvoiceId;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\UI\Http\Validations\EpochNewSaleValidation;
use Throwable;

class EpochNewSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * @param PerformEpochNewSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(
        PerformEpochNewSaleCommandHandler $handler
    ) {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('Begin Epoch transaction creation');

        try {
            EpochNewSaleValidation::validate($request);

            $rebill = null;

            if (!empty($request->input('rebill'))) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start', 0)
                );
            }
            $command = new PerformEpochNewSaleCommand(
                Log::getSessionId(),
                (string) $request->input('siteId', ''),
                (string) $request->input('siteName', ''),
                (float) $request->input('amount', 0),
                (string) $request->input('currency', ''),
                (array) $request->input('payment', []),
                $request->input('crossSales', []),
                $request->input('tax', []),
                EpochBillerChargeSettings::create(
                    (string) $request->input('billerFields.clientId', ''),
                    (string) $request->input('billerFields.clientKey', ''),
                    (string) $request->input('billerFields.clientVerificationKey', ''),
                    (string) $request->input('billerFields.redirect_url', ''),
                    (string) $request->input('billerFields.notification_url', ''),
                    InvoiceId::create()
                ),
                $this->getMember($request),
                $rebill
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (InvalidCommandException | InvalidPayloadException | InvalidBillerResponseException $e) {
            Log::logException($e);
            return $this->badRequest($e);
        } catch (Throwable $e) {
            Log::logException($e);
            return $this->internalServerError($e);
        }
    }

    /**
     * @param Request $request The request object
     * @return Member|null
     */
    private function getMember(Request $request): ?Member
    {
        $member = null;

        if ($request->has('payment.information.member')) {
            $member = new Member(
                $request->input('payment.information.member.firstName'),
                $request->input('payment.information.member.lastName'),
                $request->input('payment.information.member.userName'),
                $request->input('payment.information.member.email'),
                $request->input('payment.information.member.phone'),
                $request->input('payment.information.member.address'),
                $request->input('payment.information.member.zipCode'),
                $request->input('payment.information.member.city'),
                $request->input('payment.information.member.state'),
                $request->input('payment.information.member.country'),
                $request->input('payment.information.member.password'),
                $request->input('payment.information.member.memberId'),
            );
        }

        return $member;
    }
}
