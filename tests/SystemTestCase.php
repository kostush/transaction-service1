<?php

declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Support\Collection;
use Laravel\Lumen\Testing\TestCase;
use org\bovigo\vfs\vfsStream;
use ProBillerNG\Logger\Config\FileConfig;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;

/**
 * Class SystemTestCase
 * @package Tests
 */
abstract class SystemTestCase extends TestCase
{
    use CreatesApplication;
    use CreatesTransactionData;
    use ClearSingletons;
    use Faker;
    use LoadEnv;

    /**
     * @var string
     */
    public $newSaleUrl = '/api/v1/sale/newCard/rocketgate/session/f771f5be-88fa-4c92-a6a8-e3a6328b3d70';

    /**
     * @var array
     */
    protected $minPayload;

    /**
     * @var array
     */
    protected $fullPayload;

    /**
     * @var array
     */
    protected $existingCardMinPayload;

    /**
     * @var array
     */
    protected $existingCardFullPayload;

    /**
     * @var array
     */
    protected $existingCardWithTreeDSFullPayload;

    /**
     * Setup function, called before each test
     *
     * @return void
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConfigFormatException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerConnectionException
     * @throws \ProBillerNG\SecretManager\Exception\SecretManagerRetrieveException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->configFaker();
        $this->configLogger();
        $this->loadTestEnv();

        $this->minPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "number"          => $this->faker->creditCardNumber('Visa'),
                    "expirationMonth" => $this->faker->numberBetween(1, 12),
                    "expirationYear"  => 2030,
                    "cvv"             => (string) $this->faker->numberBetween(100, 999)
                ],
            ],
            "billerFields" => [
                "merchantId"       => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                "merchantPassword" => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                "sharedSecret"     => $this->faker->word,
                "simplified3DS"    => false,
            ],
        ];

        $merchantSiteId = $this->faker->numberBetween(1, 100);

        $this->fullPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "number"          => $this->faker->creditCardNumber('Visa'),
                    "expirationMonth" => $this->faker->numberBetween(1, 12),
                    "expirationYear"  => 2030,
                    "cvv"             => (string) $this->faker->numberBetween(100, 999),
                    "member"          => [
                        "firstName" => $this->faker->name,
                        "lastName"  => $this->faker->lastName,
                        "email"     => $this->faker->email,
                        "phone"     => $this->faker->phoneNumber,
                        "address"   => $this->faker->address,
                        "zipCode"   => $this->faker->postcode,
                        "city"      => $this->faker->city,
                        "state"     => "CA",
                        "country"   => "CA"
                    ],
                ],
            ],
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                "merchantSiteId"     => (string) $merchantSiteId,
                "merchantProductId"  => $this->faker->uuid,
                //                "merchantDescriptor" => "MBI*PROBILLER.COM-855-232-9555",
                "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
                "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true),
                "ipAddress"          => $this->faker->ipv4,
                "sharedSecret"       => $this->faker->word,
                "simplified3DS"      => false,
            ],
        ];

        $this->existingCardFullPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "cardHash" => $_ENV['ROCKETGATE_CARD_HASH_1'],
                ],
            ],
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                "merchantSiteId"     => (string) $merchantSiteId,
                "merchantProductId"  => $this->faker->uuid,
                "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
                "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true),
                "ipAddress"          => $this->faker->ipv4,
                "sharedSecret"       => $this->faker->word,
                "simplified3DS"      => false,
            ],
            "useThreeD"    => false,
            "returnUrl"    => "someUrl",
        ];

        $this->existingCardWithTreeDSFullPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "cardHash" => $_ENV['ROCKETGATE_CARD_HASH_1'],
                ],
            ],
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_5'],
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_5'],
                "merchantSiteId"     => (string) $merchantSiteId,
                "merchantProductId"  => $this->faker->uuid,
                "merchantCustomerId" => uniqid((string) $merchantSiteId, true),
                "merchantInvoiceId"  => uniqid((string) $merchantSiteId, true),
                "ipAddress"          => $this->faker->ipv4,
                "sharedSecret"       => $_ENV['ROCKETGATE_SHARED_SECRET_5'],
                "simplified3DS"      => true,
            ],
            "useThreeD"    => true,
            "returnUrl"    => "someUrl",//TODO add correct url here
        ];

        $this->existingCardMinPayload = [
            "siteId"       => $this->faker->uuid,
            "amount"       => $this->faker->randomFloat(2, 1, 100),
            "currency"     => "USD",
            "payment"      => [
                "method"      => "cc",
                "information" => [
                    "cardHash" => $_ENV['ROCKETGATE_CARD_HASH_1'],
                ],
            ],
            "billerFields" => [
                "merchantId"         => $_ENV['ROCKETGATE_MERCHANT_ID_2'],
                "merchantPassword"   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_2'],
                "merchantCustomerId" => "4165c1cddd82cce24.92280817",
                "sharedSecret"       => $this->faker->word,
                "simplified3DS"      => false,
            ],
        ];
    }

    /**
     *
     * @return void
     */
    protected function configLogger()
    {
        $fs = vfsStream::setup();

        $config = new FileConfig($fs->url() . '/system_tests.log');
        $config->setServiceName(config('app.name'));
        $config->setServiceVersion(config('app.version'));
        $config->setSessionId(uniqid('TEST-'));
        $config->setLogLevel(100);
        Log::setConfig($config);
    }

    /**
     * @return void
     */
    public function checkNetbillingTestCardInfoBeforeRunningTest(): void
    {
        $netbillingCardInfo = [
            'NETBILLING_CARD_NUMBER_2',
            'NETBILLING_CARD_EXPIRE_MONTH',
            'NETBILLING_CARD_EXPIRE_YEAR',
            'NETBILLING_CARD_CVV2',
            'NETBILLING_CARD_EXP_MONTH_YEAR'
        ];

        foreach ($netbillingCardInfo as $value) {
            if (!isset($_ENV[$value]) || empty($_ENV[$value]) || strpos($_ENV[$value], '*') !== false) {
                $this->fail(
                    'Netbilling system tests will be failed because you do not have proper values in your .env.testing file in your local.'
                );
                break;
            }
        }
    }

    /**
     * Regular teardown
     * @return void
     */
    protected function tearDown(): void
    {
        $this->clearSingleton();
        parent::tearDown();
    }
}
