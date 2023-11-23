<?php

declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Laravel\Lumen\Application;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ProBillerNG\Logger\Config\FileConfig;
use ProBillerNG\Logger\Log;

abstract class IntegrationTestCase extends TestCase
{
    use CreatesApplication;
    use CreatesTransactionData;
    use Faker;
    use LoadEnv;

    /**
     * @var Application
     */
    public $app;

    /**
     * Setup function, called before each test
     *
     * @return void
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConfigFormatException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConnectionException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerRetrieveException
     */
    protected function setUp(): void
    {
        $this->app = $this->createApplication();
        $this->configLogger();
        $this->configFaker();
        $this->loadTestEnv();

        parent::setUp();
    }

    /**
     *
     * @return void
     */
    protected function configLogger()
    {
        $fs = vfsStream::setup();

        $config = new FileConfig($fs->url() . '/integration_test.log');
        $config->setServiceName('TransactionService');
        $config->setServiceVersion('1');
        $config->setSessionId(uniqid('TEST-'));
        $config->setLogLevel(100);
        Log::setConfig($config);
    }

    /**
     * @return array
     */
    protected function getBillerResponse(): array
    {
        return [
            "avs_code"        => "X",
            "cvv2_code"       => "M",
            "status_code"     => "1",
            "processor"       => "TEST",
            "auth_code"       => "999999",
            "settle_amount"   => "1.00",
            "settle_currency" => "USD",
            "trans_id"        => "114152087528",
            "member_id"       => "114152087529",
            "auth_msg"        => "TEST APPROVED",
            "recurring_id"    => "114152103912",
            "auth_date"       => "2019-11-26 20:07:58",
        ];
    }

    /**
     * @return array
     */
    protected function getBillerRequest(): array
    {
        return [
            "transactionId"   => "9853e2c2-6bc5-3b1b-9537-80ad433819ad",
            "siteTag"         => $_ENV['NETBILLING_SITE_TAG_2'],
            "accountId"       => $_ENV['NETBILLING_ACCOUNT_ID_2'],
            "initialDays"     => "30",
            "testMode"        => true,
            "memberUsername"  => "willms.kaylie",
            "memberPassword"  => "\'~8TX3",
            "amount"          => "1.00",
            "cardNumber"      => "**************",
            "cardExpire"      => $_ENV['NETBILLING_CARD_EXP_MONTH_YEAR'],
            "cardCvv2"        => "***",
            "payType"         => "C",
            "rebillAmount"    => "0.01",
            "rebillFrequency" => "30",
            "firstName"       => "Amara",
            "lastName"        => "Larkin",
            "address"         => "",
            "zipCode"         => "h2x2l2",
            "city"            => "",
            "state"           => "QC",
            "country"         => "CA",
            "email"           => "carole92@schinner.com",
            "phone"           => "",
            "ipAddress"       => "162.211.96.53",
            "host"            => "",
            "browser"         => ""
        ];
    }

    /**
     * @return array
     */
    protected function getNBDeclinedBillerResponse(): array
    {
        return [
            "avs_code"        => "Z",
            "cvv2_code"       => "M",
            "status_code"     => "0",
            "auth_code"       => "00000",
            "reason_code2"    => "200",
            "processor"       => "TEST",
            "settle_amount"   => "51.97",
            "settle_currency" => "USD",
            "trans_id"        => "114403314095",
            "member_id"       => "114403379627",
            "auth_msg"        => "TEST DECLINED",
            "recurring_id"    => "114403396010",
            "auth_date"       => "2020-10-07 23:45:01",
        ];
    }

    /**
     * @return string[]
     */
    protected function getRGAbortedBillerResponse(): array
    {
        return [
            "merchantAccount" => "9",
            "approvedAmount"  => "18.97",
            "reasonCode"      => "311",
            "responseCode"    => "3"
        ];
    }
}
