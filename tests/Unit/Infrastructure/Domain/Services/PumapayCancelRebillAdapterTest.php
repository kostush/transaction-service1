<?php

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Pumapay\Application\Services\CancelCommandHandler;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\BillerInteractionId;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NoBillerInteractionsException;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidBillerResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\PumapayTranslator;
use Tests\UnitTestCase;

class PumapayCancelRebillAdapterTest extends UnitTestCase
{
    /**
     * @test
     * @return PumapayCancelRebillAdapter
     */
    public function it_should_return_a_valid_adapter_object(): PumapayCancelRebillAdapter
    {
        $libraryHandler = $this->createMock(CancelCommandHandler::class);
        $libraryHandler->method('execute')->willReturn(
            '{ 
               "success":true,
               "request":{
                    "businessId":"' . $_ENV['PUMAPAY_BUSINESS_ID'] . '",
                    "paymentId":"'. $this->faker->uuid . '"
               },
               "response":{
                    "success":true,
                    "status":200
               },
               "code":200,
               "reason":null
            }'
        );

        $translator = $this->createMock(PumapayTranslator::class);
        $adapter    = new PumapayCancelRebillAdapter($libraryHandler, $translator);

        $this->assertInstanceOf(PumapayCancelRebillAdapter::class, $adapter);

        return $adapter;
    }

    /**
     * @test
     * @depends it_should_return_a_valid_adapter_object
     *
     * @param PumapayCancelRebillAdapter $adapter Pumapay Adapter
     *
     * @return void
     * @throws LoggerException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws NoBillerInteractionsException
     * @throws InvalidBillerResponseException
     * @throws \Exception
     */
    public function it_should_return_pumapay_biller_response(PumapayCancelRebillAdapter $adapter): void
    {
        $transaction = $this->createChargeTransactionWithoutRebillOnPumapay();

        $transaction->addBillerInteraction(
            BillerInteraction::create(
                BillerInteraction::TYPE_RESPONSE,
                json_encode(
                    [
                        'status'   => 'approved',
                        'type'     => 'join',
                        'request'  => '',
                        'response' => [
                            'transactionData' => [
                                'statusID' => 3,
                                'typeID'   => 5,
                                'id'       => 'pZVUs7khzqn2kzweUc8ew1dAAKCgZbiJ',
                            ],
                        ],
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable(),
                BillerInteractionId::create()
            )
        );

        $result = $adapter->cancelRebill(
            $transaction,
            $_ENV['PUMAPAY_BUSINESS_ID'],
            $_ENV['PUMAPAY_API_KEY']
        );

        $this->assertInstanceOf(PumapayBillerResponse::class, $result);
    }
}
