<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use Tests\UnitTestCase;

class RocketgateBillerFieldsTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     */
    public function create_without_merchant_id_should_throw_missing_merchant_information_exception(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        RocketGateChargeSettings::create(null, '', '', '', '', '', '', '', '', '', null, null);
    }

    /**
     * @test
     * @return void
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     */
    public function create_without_merchant_password_should_throw_missing_merchant_information_exception(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        RocketGateChargeSettings::create('asd', null, '', '', '', '', '', '', '', '', null, null);
    }
}
