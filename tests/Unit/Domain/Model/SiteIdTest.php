<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\SiteId;
use Tests\UnitTestCase;

class SiteIdTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_should_return_a_site_id_object_when_corect_data_is_sent()
    {
        $siteId = SiteId::createFromString('8051d60a-7fb0-4ef2-8e60-968eee79c104');

        $this->assertInstanceOf(SiteId::class, $siteId);
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function create_site_id_should_throw_exception_when_incorrect_data_is_provided()
    {
        $this->expectException(InvalidChargeInformationException::class);

        SiteId::createFromString('8051d60a-7fb0-4ef2');
    }
}
