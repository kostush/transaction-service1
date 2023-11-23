<?php
declare(strict_types=1);

namespace Tests\Unit\Application\Service\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use Tests\UnitTestCase;

class BillerLoginInfoTest extends UnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @return BillerLoginInfo
     */
    public function it_should_create_biller_login_info_with_correct_data(): BillerLoginInfo
    {
        $billerLoginInfo = new BillerLoginInfo('usernameTest', 'passwordTest');
        $this->assertInstanceOf(BillerLoginInfo::class, $billerLoginInfo);
        return $billerLoginInfo;
    }

    /**
     * @test
     * @return void
     * @depends it_should_create_biller_login_info_with_correct_data
     * @param BillerLoginInfo $billerLoginInfo biller login information
     */
    public function it_should_contain_the_correct_username_in_getter(BillerLoginInfo $billerLoginInfo): void
    {
        $this->assertEquals('usernameTest', $billerLoginInfo->userName());
    }

    /**
     * @test
     * @return void
     * @depends it_should_create_biller_login_info_with_correct_data
     * @param BillerLoginInfo $billerLoginInfo biller login information
     */
    public function it_should_contain_the_correct_password_in_getter(BillerLoginInfo $billerLoginInfo): void
    {
        $this->assertEquals('passwordTest', $billerLoginInfo->password());
    }
}
