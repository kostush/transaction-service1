<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Qysso\Domain\Model\Exception\InvalidFieldException;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformQyssoNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\PerformQyssoNewSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\UI\Http\Validations\QyssoNewSaleValidation;

class QyssoNewSaleController extends Controller
{
    /**
     * @var TransactionalCommandHandler
     */
    protected $handler;

    /**
     * QyssoNewSaleController constructor.
     * @param PerformQyssoNewSaleCommandHandler $handler PerformQyssoNewSaleCommandHandler
     */
    public function __construct(PerformQyssoNewSaleCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param Request $request Request
     * @return JsonResponse
     * @throws Exception
     */
    public function create(Request $request): JsonResponse
    {
        Log::info('Begin Qysso transaction creation');

        try {
            QyssoNewSaleValidation::validate($request);

            $rebill = null;

            if (!empty($request->input('rebill'))) {
                $rebill = new Rebill(
                    $request->input('rebill.amount'),
                    $request->input('rebill.frequency'),
                    $request->input('rebill.start', 0)
                );
            }

            $command = new PerformQyssoNewSaleCommand(
                Log::getSessionId(),
                (string) $request->input('siteId', ''),
                (string) $request->input('siteName', ''),
                (string) $request->input('clientIp', ''),
                (float) $request->input('amount', 0),
                (string) $request->input('currency', ''),
                (array) $request->input('payment', []),
                $request->input('tax', []),
                QyssoBillerSettings::create(
                    (string) $request->input('billerFields.companyNum', ''),
                    (string) $request->input('billerFields.personalHashKey', ''),
                    (string) $request->input('billerFields.redirectUrl', ''),
                    (string) $request->input('billerFields.notificationUrl', '')
                ),
                $this->getMember($request),
                $rebill
            );

            $result = $this->handler->execute($command);

            return response()->json($result, Response::HTTP_CREATED);
        } catch (InvalidFieldException | InvalidCommandException | InvalidPayloadException | InvalidBillerResponseException $e) {
            Log::logException($e);
            return $this->badRequest($e);
        } catch (\Throwable $e) {
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
