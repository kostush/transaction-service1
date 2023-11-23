<?php

declare(strict_types=1);

namespace Tests\System\Netbilling;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Exception;
use Tests\CreateTransactionDataForNetbilling;
use Tests\SystemTestCase;
use Illuminate\Http\Response;

class PerformRetrieveNetbillingTransactionTest extends SystemTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @var string
     */
    private $uri = '/api/v1/transaction';

    private $siteTag;

    private $accountId;

    public function setUp(): void
    {
        $this->siteTag   = $_ENV['NETBILLING_SITE_TAG_2'];
        $this->accountId = $_ENV['NETBILLING_ACCOUNT_ID_2'];

        parent::setUp();
    }

    /**
     * @test
     * @return array
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws Exception
     */
    public function retrieve_new_credit_card_transaction_with_netbilling_should_return_200(): array
    {
        $transaction = $this->createNetbillingPendingTransactionWithSingleCharge();

        $repository = $this->app->make(TransactionRepository::class);
        $repository->add($transaction);

        $response = $this->get($this->uri . '/' . (string) $transaction->transactionId() . '/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70');
        $response->assertResponseStatus(Response::HTTP_OK);

        return [$response->response->getContent(), $transaction];
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_with_netbilling_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function retrieve_should_return_a_json_response_containing_the_correct_transaction_id($array): void
    {
        [$content, $transaction] = $array;
        $transactionPayload = json_decode($content);

        $this->assertEquals($transactionPayload->transaction->transaction_id, (string) $transaction->transactionId());
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_with_netbilling_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function retrieve_should_return_a_json_response_containing_the_site_tag($array): void
    {
        [$content, $transaction] = $array;
        $transactionPayload = json_decode($content);

        $this->assertEquals($this->siteTag, $transactionPayload->biller_settings->site_tag);
    }

    /**
     * @test
     * @depends retrieve_new_credit_card_transaction_with_netbilling_should_return_200
     * @param string $array The array key json
     * @return void
     */
    public function retrieve_should_return_a_json_response_containing_the_account_id($array): void
    {
        [$content, $transaction] = $array;
        $transactionPayload = json_decode($content);

        $this->assertEquals($this->accountId, $transactionPayload->biller_settings->account_id);
    }
}
