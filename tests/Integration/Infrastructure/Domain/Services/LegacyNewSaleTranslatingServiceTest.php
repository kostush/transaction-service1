<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleTranslatingService;
use Tests\IntegrationTestCase;

class LegacyNewSaleTranslatingServiceTestt extends IntegrationTestCase
{
    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     */
    public function it_should_return_a_legacy_new_sale_biller_response(): void
    {
        $translatingService = new LegacyNewSaleTranslatingService(
            app()->make(LegacyNewSaleAdapter::class)
        );

        $command = $this->createPerformLegacyNewSaleCommand();

        $charges = ChargesCollection::createFromArray($command->charges());

        $response = $translatingService->chargeNewSale(
            $this->createPendingLegacyTransaction(),
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
            ),
            $charges
        );

        $this->assertInstanceOf(LegacyNewSaleBillerResponse::class, $response);
    }
}
