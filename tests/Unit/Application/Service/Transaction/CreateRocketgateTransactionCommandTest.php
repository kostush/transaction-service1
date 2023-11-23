<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\PerformRocketgateNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use Tests\UnitTestCase;

class CreateRocketgateTransactionCommandTest extends UnitTestCase
{

    /**
     * @test
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return void
     */
    public function create_should_return_command_instance()
    {
        $command = $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill();

        $this->assertInstanceOf(PerformRocketgateNewCreditCardSaleCommand::class, $command);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function create_should_throw_exception_if_missing_site_id_given()
    {
        $this->expectException(MissingChargeInformationException::class);

        $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['siteId' => null]);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function create_should_throw_exception_if_missing_currency_id_given()
    {
        $this->expectException(MissingChargeInformationException::class);

        $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['currency' => null]);
    }

    /**
     * @test
     * @throws MissingChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function create_should_throw_exception_if_missing_amount_given()
    {
        $this->expectException(MissingChargeInformationException::class);

        $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['amount' => null]);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function create_should_throw_exception_if_invalid_amount_given()
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['amount' => 'invalidAmount']);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws \Exception
     * @return void
     */
    public function create_should_throw_exception_if_invalid_use_threed()
    {
        $this->expectException(InvalidChargeInformationException::class);

        $this->createPerformRocketgateNewCreditCardSaleCommandWithRebill(['useThreeD' => 'invalidValue']);
    }
}
