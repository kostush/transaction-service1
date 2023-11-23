<?php
declare(strict_types=1);

namespace Tests\System\Legacy;

use Illuminate\Http\Response;
use ProBillerNG\Transaction\Domain\Model\Exception\UnknownBillerNameException;
use Tests\SystemTestCase;

class RetrieveLegacyTransactionTest extends SystemTestCase
{

    const CORRELATION_ID = 'X-CORRELATION-ID';
    /**
     * @var string
     */
    private $retrieveTransactionUri = '/api/v1/transaction';


    /**
     * @var string
     */
    private $uri = '/api/v1/sale/biller/vendo/session/b0c56eaf-0ce8-4884-9bae-076c69a2b0af';

    /**
     * @return array
     */
    public static function payload(): array
    {
        return [
            'payment'      => [
                'type'        => 'cc',
                'method'      => 'visa',
                'information' => [
                    'member' => [
                        "firstName" => "Vendo",
                        "lastName"  => "sdasdsdsd",
                        "userName"  => "asdasdadad",
                        "password"  => "123456test",
                        "email"     => "test@test.mindgeek.com",
                        "phone"     => "514 000-0000",
                        "address"   => "7777 Decarie",
                        "zipCode"   => "H1H1H1",
                        "city"      => "Montreal",
                        "state"     => "QC",
                        "country"   => "CA"
                    ]
                ]
            ],
            'charges'      => [
                [
                    'siteId'         => '8e34c94e-135f-4acb-9141-58b3a6e56c74',
                    "amount"         => "14.97",
                    "currency"       => "USD",
                    "productId"      => 15,
                    "isMainPurchase" => true,
                    'rebill'         => [
                        'amount'    => "10",
                        'frequency' => 365,
                        'start'     => 30
                    ],
                    'tax'            => [
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
                    ]
                ]
            ],
            'billerFields' => [
                'legacyMemberId' => 101988,
                'returnUrl'      => 'http://purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt',
                'postbackUrl'    => 'http://postback-purchase-gateway.probiller.com/api/v1/purchase/thirdParty/return/jwt'
            ]
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_created_for_successful_pending_transaction():array
    {
        $response = $this->json(
            'POST',
            $this->uri,
            self::payload()
        );
        $response->assertResponseStatus(Response::HTTP_CREATED);
        return json_decode($response->response->getContent(), true);
    }

    /**
     * @test
     * @depends it_should_return_created_for_successful_pending_transaction
     * @param string $responseContent The array key json
     * @return array
     */
    public function it_should_return_a_transaction_id_for_successful_pending_transaction($responseContent):array
    {
        $transactionId = $responseContent['transactionId'];
        $response      = $this->get($this->retrieveTransactionUri . '/' . (string) $transactionId . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        return [$response->response->getContent(), $transactionId];
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_id_for_successful_pending_transaction
     * @param array $returnArray The array key json
     * @return void
     */
    public function it_should_contain_a_correct_transaction_id(array $returnArray): void
    {
        [$content, $transactionId] = $returnArray;
        $transactionPayload        = json_decode($content);

        $this->assertIsObject($transactionPayload->transaction);
        $this->assertEquals($transactionPayload->transaction->transaction_id, (string) $transactionId);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_id_for_successful_pending_transaction
     * @param array $returnArray The array key json
     * @return void
     */
    public function it_should_contain_member_info(array $returnArray): void
    {
        [$content, $transactionId] = $returnArray;
        $transactionPayload        = json_decode($content);

        $this->assertIsObject($transactionPayload->member);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_id_for_successful_pending_transaction
     * @param array $returnArray The array key json
     * @return void
     */
    public function it_should_contain_biller_settings(array $returnArray): void
    {
        [$content, $transactionId] = $returnArray;
        $transactionPayload        = json_decode($content);

        $this->assertIsObject($transactionPayload->biller_settings);
    }

    /**
     * @test
     * @depends it_should_return_a_transaction_id_for_successful_pending_transaction
     * @param array $returnArray The array key json
     * @return void
     */
    public function it_should_contain_biller_transactions(array $returnArray): void
    {
        [$content, $transactionId] = $returnArray;
        $transactionPayload        = json_decode($content);

        $this->assertIsArray($transactionPayload->biller_transactions);
    }
}
