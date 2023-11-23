<?php

declare(strict_types=1);

namespace Tests\Unit\Infastructure\Domain\Services;

use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillTranslatingService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillTranslator;
use Tests\UnitTestCase;

class RocketgateUpdateRebillTranslatingServiceTest extends UnitTestCase
{
    /**
     * @var RocketgateUpdateRebillTranslator
     */
    private $rocketgateService;

    /**
     * @var RocketgateUpdateRebillTranslatingService
     */
    private $service;

    /**
     * Setup before test
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rocketgateService = $this->createMock(RocketgateUpdateRebillTranslator::class);
        $this->service           = new RocketgateUpdateRebillTranslatingService($this->rocketgateService);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     */
    public function start_should_call_rocketgate_update_rebill_service()
    {
        $this->rocketgateService->expects($this->once())->method('start');

        $this->service->start(
            $this->createUpdateRebillTransaction()
        );
    }
}
