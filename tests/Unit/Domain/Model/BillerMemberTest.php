<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\BillerMember;
use Tests\UnitTestCase;

class BillerMemberTest extends UnitTestCase
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
     * @return BillerMember
     */
    public function it_should_create_biller_member_with_correct_inputs(): BillerMember
    {
        $billerMember = BillerMember::create('username', 'password');
        $this->assertInstanceOf(BillerMember::class, $billerMember);
        return $billerMember;
    }

    /**
     * @test
     * @depends it_should_create_biller_member_with_correct_inputs
     * @param BillerMember $billerMember biller member information
     * @return void
     */
    public function it_should_return_the_correct_username_on_getter(BillerMember $billerMember): void
    {
        $this->assertEquals('username', $billerMember->userName());
    }

    /**
     * @test
     * @depends it_should_create_biller_member_with_correct_inputs
     * @param BillerMember $billerMember biller member information
     * @return void
     */
    public function it_should_return_the_correct_password_on_getter(BillerMember $billerMember): void
    {
        $this->assertEquals('password', $billerMember->password());
    }
}