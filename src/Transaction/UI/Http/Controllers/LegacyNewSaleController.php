<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\UI\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\LegacyNewSaleCommandHttpDTO;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidCommandException;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\PerformLegacyNewSaleCommand;
use ProBillerNG\Transaction\Application\Services\Transaction\Legacy\PerformLegacyNewSaleCommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Application\Services\TransactionalCommandHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\UI\Http\Requests\LegacyNewSaleRequest;

class LegacyNewSaleController extends Controller
{
    /**
     * @var PerformLegacyNewSaleCommandHandler
     */
    protected $handler;

    /**
     * @param PerformLegacyNewSaleCommandHandler $handler Non-transactional Command Handler
     */
    public function __construct(PerformLegacyNewSaleCommandHandler $handler)
    {
        $this->handler = new TransactionalCommandHandler($handler);
    }

    /**
     * @param LegacyNewSaleRequest $request    Request
     * @param string               $billerName Biller Name
     * @return JsonResponse
     * @throws Exception
     */
    public function create(LegacyNewSaleRequest $request, string $billerName): JsonResponse
    {
        Log::info('Begin Legacy transaction creation');

        try {
            $command = new PerformLegacyNewSaleCommand(
                $request->input('payment.type'),
                $request->input('charges'),
                $request->input('billerFields.returnUrl'),
                $request->input('billerFields.postbackUrl'),
                $billerName,
                $request->input('payment.method'),
                $this->getMember($request),
                $request->input('billerFields.legacyMemberId'),
                $request->input('billerFields.others')
            );

            /** @var LegacyNewSaleCommandHttpDTO $result */
            $result = $this->handler->execute($command);

            return response()->json($result, $result->responseStatus());
        } catch (InvalidCommandException | InvalidPayloadException | InvalidBillerResponseException $e) {
            Log::logException($e);
            return $this->badRequest($e);
        } catch (\Throwable $e) {
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
            );
        }

        return $member;
    }
}
