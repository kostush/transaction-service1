<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingRebillUpdateSettings;
use Tests\UnitTestCase;

class NetbillingRebillUpdateSettingsTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function it_should_throw_an_exception_when_empty_accountId_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);
        NetbillingRebillUpdateSettings::create(
            'siteTag',
            '',
            '123456789',
            'password',
        );
    }

    /**
     * @test
     * @return void
     */
    public function it_should_throw_an_exception_when_empty_merchantPassword_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);
        NetbillingRebillUpdateSettings::create(
            'siteTag',
            '12345678',
            '123456789',
            '',
        );
    }


    /**
     * @test
     * @return void
     */
    public function it_should_throw_an_exception_when_empty_siteTag_provided()
    {
        $this->expectException(MissingMerchantInformationException::class);
        NetbillingRebillUpdateSettings::create(
            '',
            '12345678',
            '123456789',
            'password',
        );
    }
}
