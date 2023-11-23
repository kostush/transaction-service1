<?php

namespace Tests\Integration\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerInteractionsReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Rocketgate\RocketgateBillerTransactionCollection;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use Tests\IntegrationTestCase;

class RocketgateBillerInteractionsReturnTypeTest extends IntegrationTestCase
{
    /**
     * @test
     * @return RocketgateBillerInteractionsReturnType
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    public function create_from_biller_interactions_collection_should_return_a_rocketgate_biller_interactions_object(): RocketgateBillerInteractionsReturnType
    {
        $result = RocketgateBillerInteractionsReturnType::createFromBillerInteractionsCollection(
            $this->billerInteractionCollection(),true
        );

        $this->assertInstanceOf(RocketgateBillerInteractionsReturnType::class, $result);

        return $result;
    }

    /**
     * @test
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @return array
     */
    public function sort_biller_interactions_should_return_a_collection_array_ordered_by_date(): array
    {
        //move the last interaction to the beginning of the array
        $interactionsArray = $this->billerInteractionCollection()->toArray();

        /** @var BillerInteraction $lastInteraction */
        $lastInteraction = array_pop($interactionsArray);
        array_unshift($interactionsArray, $lastInteraction);

        RocketgateBillerInteractionsReturnType::sortBillerInteractions($interactionsArray);

        //assert the last interaction was moved back to the end of the array
        $this->assertSame($lastInteraction->payload(), end($interactionsArray)->payload());

        return $interactionsArray;
    }

    /**
     * @test
     * @param array $interactionsArray the interactions array
     * @depends sort_biller_interactions_should_return_a_collection_array_ordered_by_date
     * @return array
     */
    public function get_request_interactions_should_return_only_the_biller_interaction_requests(
        array $interactionsArray
    ): array {
        $requests = RocketgateBillerInteractionsReturnType::getRequestInteractions($interactionsArray);

        $responseFound = false;
        /** @var BillerInteraction $request */
        foreach ($requests as $request) {
            if ($request->type() !== BillerInteraction::TYPE_REQUEST) {
                $responseFound = true;
            }
        }

        $this->assertFalse($responseFound);

        return $requests;
    }

    /**
     * @test
     * @param array $requestsArray The interactions array
     * @depends get_request_interactions_should_return_only_the_biller_interaction_requests
     * @return void
     */
    public function is_three_d_secured_initial_request_should_return_the_correct_flag(array $requestsArray): void
    {
        $this->assertTrue(
            RocketgateBillerInteractionsReturnType::isThreeDSecuredInitialRequest($requestsArray)
        );
    }

    /**
     * @test
     * @param array $interactionsArray the interactions array
     * @depends sort_biller_interactions_should_return_a_collection_array_ordered_by_date
     * @return array
     */
    public function get_response_interactions_should_return_only_the_biller_interaction_responses(
        array $interactionsArray
    ): array {
        $interactions = RocketgateBillerInteractionsReturnType::getResponseInteractions($interactionsArray);

        $requestFound = false;
        /** @var BillerInteraction $request */
        foreach ($interactions as $interaction) {
            if ($interaction->type() !== BillerInteraction::TYPE_RESPONSE) {
                $requestFound = true;
            }
        }

        $this->assertFalse($requestFound);

        return $interactions;
    }

    /**
     * @test
     * @param array $responsesArray The interactions array
     * @depends get_response_interactions_should_return_only_the_biller_interaction_responses
     * @return void
     */
    public function is_three_d_secured_transaction_should_return_the_correct_flag(array $responsesArray): void
    {
        $this->assertTrue(
            RocketgateBillerInteractionsReturnType::authRequiredForThreeD(reset($responsesArray))
        );
    }

    /**
     * @test
     * @param RocketgateBillerInteractionsReturnType $billerInteractions The rocketgate biller interactions object
     * @depends create_from_biller_interactions_collection_should_return_a_rocketgate_biller_interactions_object
     * @return void
     */
    public function the_returned_object_should_have_a_biller_transactions_collection(
        RocketgateBillerInteractionsReturnType $billerInteractions
    ): void {
        $this->assertInstanceOf(
            RocketgateBillerTransactionCollection::class,
            $billerInteractions->billerTransactions()
        );
    }

    /**
     * @test
     * @param RocketgateBillerInteractionsReturnType $billerInteractions The rocketgate biller interactions object
     * @depends create_from_biller_interactions_collection_should_return_a_rocketgate_biller_interactions_object
     * @return void
     */
    public function the_returned_object_should_have_a_card_hash(
        RocketgateBillerInteractionsReturnType $billerInteractions
    ): void {
        $this->assertSame($this->cardHash(), $billerInteractions->cardHash());
    }

    /**
     * @test
     * @param RocketgateBillerInteractionsReturnType $billerInteractions The rocketgate biller interactions object
     * @depends create_from_biller_interactions_collection_should_return_a_rocketgate_biller_interactions_object
     * @return void
     */
    public function the_returned_object_should_have_a_card_description(
        RocketgateBillerInteractionsReturnType $billerInteractions
    ): void {
        $this->assertSame($this->cardDescription(), $billerInteractions->cardDescription());
    }

    /**
     * @test
     * @param RocketgateBillerInteractionsReturnType $billerInteractions The rocketgate biller interactions object
     * @depends create_from_biller_interactions_collection_should_return_a_rocketgate_biller_interactions_object
     * @return void
     */
    public function the_returned_object_should_have_a_card_three_d_usage_flag(
        RocketgateBillerInteractionsReturnType $billerInteractions
    ): void {
        $this->assertSame(true, $billerInteractions->threeDSecured());
    }
}
