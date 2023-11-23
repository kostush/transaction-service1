<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Email;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use Tests\UnitTestCase;

class EmailTest extends UnitTestCase
{
    /**
     * @test
     * @return Email
     * @throws \Exception
     */
    public function create_should_return_a_email_when_correct_data_is_provided(): Email
    {
        $email = $this->createEmail(['email' => 'initial@email.com']);

        $this->assertInstanceOf(Email::class, $email);

        return $email;
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function create_should_return_a_email_when_correct_data_is_provided_even_with_special_characters(): void
    {
        $email = $this->createEmail(['email' => '이메일@email.com']);

        $this->assertInstanceOf(Email::class, $email);
    }

    /**
     * @test
     * @return Email
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function create_email_should_throw_exception_when_incorrect_data_is_provided(): Email
    {
        $this->expectException(InvalidCreditCardInformationException::class);

        $email = $this->createEmail(['email' => 'invalidEmail']);

        return $email;
    }

    /**
     * @test
     * @depends create_should_return_a_email_when_correct_data_is_provided
     * @param Email $email Email object
     * @return void
     * @throws \Exception
     */
    public function email_should_return_true_when_equal(Email $email): void
    {
        $equalEmail = $this->createEmail(['email' => $email->email()]);

        $this->assertTrue($email->equals($equalEmail));
    }

    /**
     * @test
     * @depends create_should_return_a_email_when_correct_data_is_provided
     * @param Email $email Email object
     * @return void
     * @throws \Exception
     */
    public function email_should_return_false_when_equal(Email $email): void
    {
        $equalEmail = $this->createEmail(['email' => 'different@email.com']);

        $this->assertFalse($email->equals($equalEmail));
    }
}
