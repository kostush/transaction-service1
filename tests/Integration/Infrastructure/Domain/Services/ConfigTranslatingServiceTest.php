<?php

declare(strict_types=1);

namespace Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraDataNetbilling;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraDataRocketgate;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigServiceClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigTranslatingService;
use ReflectionClass;
use ReflectionException;
use Tests\CreatesTransactionData;
use Tests\IntegrationTestCase;

class ConfigTranslatingServiceTest extends IntegrationTestCase
{
    use CreatesTransactionData;

    /**
     * @var ConfigTranslatingService
     */
    protected $translatingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translatingService = new ConfigTranslatingService(app()->make(ConfigServiceClient::class));
    }

    /**
     * @test
     * @return DeclinedBillerResponseExtraDataRocketgate
     * @throws ReflectionException
     */
    public function it_should_return_a_declined_biller_response_extra_data_object_for_rocketgate(): DeclinedBillerResponseExtraDataRocketgate
    {
        $reflection = new ReflectionClass($this->translatingService);
        $method = $reflection->getMethod('translateUnifiedGroupErrorResponse');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->translatingService, [
            $this->createBillerUnifiedGroupErrorResponse(RocketGateBillerSettings::ROCKETGATE),
            RocketGateBillerSettings::ROCKETGATE
        ]);

        self::assertInstanceOf(DeclinedBillerResponseExtraDataRocketgate::class, $result);

        return $result;
    }

    /**
     * @test
     * @depends it_should_return_a_declined_biller_response_extra_data_object_for_rocketgate
     * @param DeclinedBillerResponseExtraDataRocketgate $billerResponseExtraData
     */
    public function it_should_contain_reason_code_in_biller_response_for_rocketgate(DeclinedBillerResponseExtraDataRocketgate $billerResponseExtraData): void
    {
        self::assertSame('reasonCode1', $billerResponseExtraData->getReasonCode());
    }

    /**
     * @test
     * @depends it_should_return_a_declined_biller_response_extra_data_object_for_rocketgate
     * @param DeclinedBillerResponseExtraDataRocketgate $billerResponseExtraData
     */
    public function it_should_contain_bank_response_code_in_biller_response_for_rocketgate(DeclinedBillerResponseExtraDataRocketgate $billerResponseExtraData): void
    {
        self::assertSame('bankResponseCode1', $billerResponseExtraData->getBankResponseCode());
    }

    /**
     * @test
     * @return DeclinedBillerResponseExtraDataNetbilling
     * @throws ReflectionException
     */
    public function it_should_return_a_declined_biller_response_extra_data_object_for_netbilling(): DeclinedBillerResponseExtraDataNetbilling
    {
        $reflection = new ReflectionClass($this->translatingService);
        $method = $reflection->getMethod('translateUnifiedGroupErrorResponse');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->translatingService, [
            $this->createBillerUnifiedGroupErrorResponse(NetbillingBillerSettings::NETBILLING),
            NetbillingBillerSettings::NETBILLING
        ]);

        self::assertInstanceOf(DeclinedBillerResponseExtraDataNetbilling::class, $result);

        return $result;
    }

    /**
     * @test
     * @depends it_should_return_a_declined_biller_response_extra_data_object_for_netbilling
     * @param DeclinedBillerResponseExtraDataNetbilling $billerResponseExtraData
     */
    public function it_should_contain_an_auth_message_in_biller_response_for_netbilling(DeclinedBillerResponseExtraDataNetbilling $billerResponseExtraData): void
    {
        self::assertSame('authMessage1', $billerResponseExtraData->getAuthMessage());
    }

    /**
     * @test
     * @depends it_should_return_a_declined_biller_response_extra_data_object_for_netbilling
     * @param DeclinedBillerResponseExtraDataNetbilling $billerResponseExtraData
     */
    public function it_should_contain_a_processor_in_biller_response_for_netbilling(DeclinedBillerResponseExtraDataNetbilling $billerResponseExtraData): void
    {
        self::assertSame('processor1', $billerResponseExtraData->getProcessor());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function it_should_return_null_when_given_a_null_response(): void
    {
        $reflection = new ReflectionClass($this->translatingService);
        $method = $reflection->getMethod('translateUnifiedGroupErrorResponse');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->translatingService, [null, RocketGateBillerSettings::ROCKETGATE]);

        self::assertNull($result);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function it_should_return_null_when_given_an_invalid_biller(): void
    {
        $reflection = new ReflectionClass($this->translatingService);
        $method = $reflection->getMethod('translateUnifiedGroupErrorResponse');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->translatingService, [
            $this->createBillerUnifiedGroupErrorResponse(NetbillingBillerSettings::NETBILLING),
            QyssoBillerSettings::QYSSO
        ]);

        self::assertNull($result);
    }
}
