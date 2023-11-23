<?php
declare(strict_types=1);

namespace Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Exception\InvalidInitialDaysWithRebillInfoException;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Exception;
use Tests\CreateTransactionDataForNetbilling;
use Tests\UnitTestCase;

/**
 * Class PerformNetbillingSaleCommandTest
 * @package Application\Service\Transaction
 */
class PerformNetbillingSaleCommandTest extends UnitTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @test
     * @return void
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function create_should_return_command_instance_of_netbilling_sale_command()
    {
        $command = $this->createPerformNetbillingSaleCommandSingleCharge();

        $this->assertInstanceOf(PerformNetbillingNewCreditCardSaleCommand::class, $command);
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws Exception
     */
    public function it_should_throw_exception_when_zero_initaldays_passed_with_rebill_info()
    {
        $this->expectException(InvalidInitialDaysWithRebillInfoException::class);
        $this->createPerformNetbillingNewCreditCardSaleCommandWithRebill(['initialDays' => 0]);

    }
}