<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use Tests\UnitTestCase;

class LegacyBillerChargeSettingsTest extends UnitTestCase
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
     * @return void
     */
    public function it_should_transaction_id_to_custom_fields_when_there_is_already_custom_fields(): void
    {
        $transactionId  = $this->faker->uuid;
        $mainProductId  = $this->faker->numberBetween();
        $previousCustom = ['custom' => ['mainProductId' => $mainProductId]];

        $legaciBillerChargeSettings = LegacyBillerChargeSettings::create(
            null,
            'Rocketgate',
            'returnUrl',
            'postbackUrl',
            $previousCustom
        );

        $legaciBillerChargeSettings->addTransactionIdToCustomFields($transactionId);

        $custom = $legaciBillerChargeSettings->others();

        $expectedCustom['custom'] = [
            'mainProductId' => $mainProductId,
            'transactionId' => $transactionId
        ];

        $this->assertEquals($expectedCustom, $custom);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_add_transaction_id_to_custom_fields_when_there_is_no_custom_fields(): void
    {
        $transactionId = $this->faker->uuid;

        $legaciBillerChargeSettings = LegacyBillerChargeSettings::create(
            null,
            'Rocketgate',
            'returnUrl',
            'postbackUrl',
            null
        );

        $legaciBillerChargeSettings->addTransactionIdToCustomFields($transactionId);

        $custom = $legaciBillerChargeSettings->others();

        $expectedCustom['custom'] = [
            'transactionId' => $transactionId
        ];

        $this->assertEquals($expectedCustom, $custom);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_add_main_product_id_field_when_there_is_already_custom_fields(): void
    {
        $transactionId  = $this->faker->uuid;
        $previousCustom = ['custom' => ['transactionId' => $transactionId]];
        $mainProductId  = $this->faker->numberBetween();

        $legaciBillerChargeSettings = LegacyBillerChargeSettings::create(
            null,
            'Rocketgate',
            'returnUrl',
            'postbackUrl',
            $previousCustom
        );

        $legaciBillerChargeSettings->addMainProductIdToCustomFields($mainProductId);

        $custom = $legaciBillerChargeSettings->others();

        $expectedCustom['custom'] = [
            'mainProductId' => $mainProductId,
            'transactionId' => $transactionId
        ];

        $this->assertEquals($expectedCustom, $custom);
    }

    /**
     * @test
     * @return void
     */
    public function it_should_add_main_product_id_custom_field_when_there_is_no_custom_fields(): void
    {
        $mainProductId = $this->faker->numberBetween();

        $legaciBillerChargeSettings = LegacyBillerChargeSettings::create(
            null,
            'Rocketgate',
            'returnUrl',
            'postbackUrl',
            null
        );

        $legaciBillerChargeSettings->addMainProductIdToCustomFields($mainProductId);

        $custom = $legaciBillerChargeSettings->others();

        $expectedCustom['custom'] = [
            'mainProductId' => $mainProductId
        ];

        $this->assertEquals($expectedCustom, $custom);
    }
}
