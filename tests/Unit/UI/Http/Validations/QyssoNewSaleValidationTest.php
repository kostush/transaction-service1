<?php

declare(strict_types=1);

namespace Tests\Unit\UI\Http\Validations;

use Illuminate\Http\Request;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\UI\Http\Validations\QyssoNewSaleValidation;
use Tests\UnitTestCase;

class QyssoNewSaleValidationTest extends UnitTestCase
{
    protected $requestData = [
        'sessionId'    => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
        'siteId'       => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
        'siteName'     => 'www.realitykings.com',
        'clientIp'     => '10.10.10.123',
        'amount'       => 14.97,
        'currency'     => 'USD',
        'payment'      => [
            'type'        => 'cc',
            'method'      => 'visa',
            'information' => [
                'member' => [
                    'userName'  => 'username',
                    'password'  => 'password',
                    'firstName' => 'firstName',
                    'lastName'  => 'lastName',
                    'email'     => 'email@test.mindgeek.com',
                    'zipCode'   => 'zipCode',
                ]
            ]
        ],
        'rebill'       => [
            'amount'    => 10,
            'frequency' => 365,
            'start'     => 30
        ],
        'tax'          => [
            'initialAmount'    => [
                'beforeTaxes' => 14.23,
                'taxes'       => 0.74,
                'afterTaxes'  => 14.97
            ],
            'rebillAmount'     => [
                'beforeTaxes' => 9.5,
                'taxes'       => 0.5,
                'afterTaxes'  => 10
            ],
            'taxApplicationId' => '60bf5bcb-ac64-496c-acc5-9c7cf54a1869',
            'taxName'          => 'Tax Name',
            'taxRate'          => 0.05,
            'taxType'          => 'vat'
        ],
        'billerFields' => [
            "companyNum"      => "companyNum",
            "personalHashKey" => "fakepassword",
            "notificationUrl" => "http=>//ams-postback-capture-service.probiller.com/api/postbacks/41",
            "redirectUrl"     => "https=>//redirect-url-to-client"
        ]
    ];


    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_evaluate_as_true_when_the_request_data_is_valid(): void
    {
        $request = new Request(
            $this->requestData
        );

        QyssoNewSaleValidation::validate($request);

        $this->assertTrue(true);
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_throw_invalid_charge_information_exception_when_the_request_is_not_valid(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $request = $this->requestData;
        unset($request['siteId']);

        $request = (new Request())->replace($request);

        QyssoNewSaleValidation::validate($request);
    }

    /**
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_throw_invalid_charge_information_exception_when_site_name_is_missing(): void
    {
        $this->expectException(InvalidChargeInformationException::class);

        $request = $this->requestData;
        unset($request['siteName']);

        $request = (new Request())->replace($request);

        QyssoNewSaleValidation::validate($request);
    }
}
