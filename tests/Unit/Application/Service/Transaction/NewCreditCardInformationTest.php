<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\NewCreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use Tests\UnitTestCase;

class NewCreditCardInformationTest extends UnitTestCase
{
    /**
     * @test
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_without_number_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new NewCreditCardInformation(null, '10', 2022, '', null);
    }


    /**
     * @test
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_without_expiration_month_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new NewCreditCardInformation($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], null, 2022, '', null);
    }

    /**
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @return void
     */
    public function create_with_string_expiration_month_should_return_an_information_object()
    {
        $information = new NewCreditCardInformation($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], '08', 2022, '02', null);
        $this->assertInstanceOf(NewCreditCardInformation::class, $information);
    }

    /**
     * @test
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_without_expiration_year_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new NewCreditCardInformation($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], '10', null, '', null);
    }

    /**
     * @test
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function create_without_cvv_should_throw_missing_credit_card_information_exception()
    {
        $this->expectException(MissingCreditCardInformationException::class);
        new NewCreditCardInformation($_ENV['ROCKETGATE_COMMON_CARD_NUMBER'], '10', 2022, null, null);
    }
}
