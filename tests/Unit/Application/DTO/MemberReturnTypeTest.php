<?php

declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\MemberReturnType;
use Tests\UnitTestCase;

class MemberReturnTypeTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     * @return array
     */
    public function create_should_return_a_transaction_payload_member_when_correct_data_is_provided(): array
    {
        $ccInfo = $this->createCreditCardInformation();

        $transactionPayloadMember = MemberReturnType::createFromCreditCardInfo($ccInfo);

        $this->assertInstanceOf(MemberReturnType::class, $transactionPayloadMember);

        $reflection = new \ReflectionClass($transactionPayloadMember);
        $props      = $reflection->getProperties();

        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $result[$prop->getName()] = $prop->getValue($transactionPayloadMember);
        }

        return [$ccInfo, $result];
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_email($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardOwner()->ownerEmail()->email(), $result['email']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_phone_number($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerPhoneNo(), $result['phoneNumber']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_first_name($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardOwner()->ownerFirstName(), $result['firstName']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_last_name($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardOwner()->ownerLastName(), $result['lastName']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_address($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerAddress(), $result['address']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_city($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerCity(), $result['city']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_state($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerState(), $result['state']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_zip($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerZip(), $result['zip']);
    }

    /**
     * @test
     * @param array $creationData Transaction Member Payload and Payload
     * @depends create_should_return_a_transaction_payload_member_when_correct_data_is_provided
     * @return void
     */
    public function transaction_payload_should_return_provided_country($creationData): void
    {
        list($ccInfo, $result) = $creationData;
        $this->assertEquals($ccInfo->creditCardBillingAddress()->ownerCountry(), $result['country']);
    }
}
