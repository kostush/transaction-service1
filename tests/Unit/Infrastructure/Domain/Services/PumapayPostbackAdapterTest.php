<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Pumapay\Application\Services\PostbackCommandHandler;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayPostbackAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\UnitTestCase;

class PumapayPostbackAdapterTest extends UnitTestCase
{
    /**
     * @test
     * @return PumapayPostbackAdapter
     */
    public function it_should_return_a_valid_adapter_object(): PumapayPostbackAdapter
    {
        $libHandler = $this->createMock(PostbackCommandHandler::class);
        $libHandler->method('execute')->willReturn(
            json_encode(
                [
                    'status' => PostbackResponse::CHARGE_RESULT_APPROVED,
                    'type'   => PostbackResponse::CHARGE_TYPE_JOIN,
                ],
                JSON_THROW_ON_ERROR
            )
        );

        $translator = $this->createMock(PumapayTranslator::class);

        $adapter = new PumapayPostbackAdapter($libHandler, $translator);

        $this->assertInstanceOf(PumapayPostbackAdapter::class, $adapter);

        return $adapter;
    }

    /**
     * @test
     * @depends it_should_return_a_valid_adapter_object
     * @param PumapayPostbackAdapter $adapter Pumapay Adapter
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_return_pumapay_biller_response(PumapayPostbackAdapter $adapter)
    {
        $result = $adapter->getTranslatedPostback('json postback string from pumapay', 'join');

        $this->assertInstanceOf(PumapayBillerResponse::class, $result);
    }
}
