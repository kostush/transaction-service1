<?php

declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use Exception;
use ProBillerNG\Epoch\Domain\Model\Exception\MissingNewSaleInformationException;
use ProBillerNG\Pumapay\Domain\Model\PostbackResponse;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoReturnBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\QyssoNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\QyssoTranslatingService;
use Tests\CreatesTransactionData;
use Tests\IntegrationTestCase;

class QyssoTranslatingServiceTest extends IntegrationTestCase
{
    use CreatesTransactionData;

    /**
     * @var string Hash Key used to validate that payload's have not been tampered with. This hash key was used to
     *             generate the signatures of the payloads in this test case.
     */
    protected $personalHashKey;

    /** @var string */
    protected $rebillPayload = '{"reply_code":"553","reply_desc":"3D Secure Redirection is needed","trans_id":"5","trans_date":"12\/21\/2020 6:10:39 PM","trans_amount":"55.3","trans_currency":"1","trans_order":"6f601b4e-e627-4f6e-9a69-eb488a8a4499","merchant_id":"7162012","client_fullname":"Test Test","client_phone":null,"client_email":"testqysso78@probiller.mindgeek.com","payment_details":"Visa .... 0000","signature":"axYHMbOE5cJPLDzsBS9hSD\/UFVWkWVuUb3oWFqbLcYU="}';

    /** @var string */
    protected $joinPayload = '{"TransType":"0","Reply":"553","TransID":"218","Date":"28/12/2020 18:15:22","Order":"c0da7a30-63e4-40b7-a23c-c337662ab434","Amount":"55.30","Payments":"1","Currency":"1","ConfirmationNum":"","Comment":"","ReplyDesc":"3D Secure Redirection is needed","CCType":"Visa","Descriptor":"","RecurringSeries":"","Last4":"0000","ccStorageID":"","Source":"SILENTPOST","WalletID":"","D3Redirect":"https://process.qysso.com/member/remoteCharge_Back.asp?TransID=218&CompanyNum=7162012","signType":"SHA256","signature":"oQr05hK0odmLqf0CRYno9x1ixugu1wOxqYFV9/h0fyg="}';

    public function setUp(): void
    {
        $this->personalHashKey = $_ENV['QYSSO_PERSONAL_HASH_KEY_3'];

        parent::setUp();
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_return_response_with_the_exact_code_for_join(): void
    {
        $this->assertEquals(
            QyssoReturnBillerResponse::STATUS_WAITING_FOR_REDIRECT,
            $this->createQyssoJoinPostbackResponse()->code()
        );
    }

    /**
     * @return QyssoBillerResponse
     * @throws Exception
     */
    protected function createQyssoJoinPostbackResponse(): QyssoBillerResponse
    {
        $response = $this->createQyssoBillerResponse(PostbackResponse::CHARGE_TYPE_JOIN);

        $this->assertInstanceOf(QyssoReturnBillerResponse::class, $response);

        return $response;
    }

    /**
     * @param string $transactionType PostbackResponse value
     * @return QyssoBillerResponse
     * @throws Exception
     */
    private function createQyssoBillerResponse(string $transactionType): QyssoBillerResponse
    {
        $translatingService = new QyssoTranslatingService(
            app()->make(QyssoNewSaleAdapter::class)
        );

        return $translatingService->translatePostback(
            $transactionType == PostbackResponse::CHARGE_TYPE_JOIN ? $this->joinPayload : $this->rebillPayload,
            $this->personalHashKey,
            $transactionType
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_return_response_with_the_exact_code_for_rebill(): void
    {
        $this->assertEquals(
            QyssoReturnBillerResponse::STATUS_WAITING_FOR_REDIRECT,
            $this->createQyssoRebillPostbackResponse()->code()
        );
    }

    /**
     * @return QyssoBillerResponse
     * @throws Exception
     */
    protected function createQyssoRebillPostbackResponse(): QyssoBillerResponse
    {
        $response = $this->createQyssoBillerResponse(PostbackResponse::CHARGE_TYPE_REBILL);

        $this->assertInstanceOf(QyssoPostbackBillerResponse::class, $response);

        return $response;
    }

    /**
     * @test
     * @return void
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws MissingChargeInformationException|Exception
     */
    public function it_should_return_a_qysso_new_sale_biller_response(): void
    {
        $this->markTestSkipped('We need to skip this test for now as Qysso url https://process.qysso.com/member/remote_charge.asp? is not working');
        $translatingService = new QyssoTranslatingService(
            app()->make(QyssoNewSaleAdapter::class)
        );

        $response = $translatingService->chargeNewSale(
            [
                $this->createPendingQyssoTransaction(),
                $this->createPendingQyssoTransaction(null, true),
            ],
            [
                [],
                []
            ],
            $this->faker->uuid,
            $this->faker->ipv4,
            new Member(
                $this->faker->name,
                $this->faker->lastName,
                $this->faker->userName,
                $this->faker->email,
                $this->faker->phoneNumber,
                $this->faker->address,
                $this->faker->postcode,
                $this->faker->city,
                'state',
                'country',
                'somePassword'
            )
        );

        $this->assertInstanceOf(QyssoNewSaleBillerResponse::class, $response);
    }
}
