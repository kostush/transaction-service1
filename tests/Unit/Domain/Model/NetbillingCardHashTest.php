<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use Tests\UnitTestCase;

class NetbillingCardHashTest extends UnitTestCase
{

    private $validEncodedCardHashString;
    private $validDecodedCardHashString;

    public function setUp(): void
    {
        $this->validEncodedCardHashString = $_ENV['NETBILLING_CARD_HASH'];
        $this->validDecodedCardHashString = 'CS:114621813929:' . $_ENV['NETBILLING_CARD_LAST_FOUR'];

        parent::setUp();
    }

    /**
     * @test
     * @return NetbillingCardHash
     * @throws \Exception
     */
    public function it_should_return_a_netbilling_card_hash_object(): NetbillingCardHash
    {
        $cardHash = NetbillingCardHash::create($this->validEncodedCardHashString);

        $this->assertInstanceOf(NetbillingCardHash::class, $cardHash);
        return $cardHash;
    }

    /**
     * @test
     * @param NetbillingCardHash $cardHash Card hash object
     * @depends it_should_return_a_netbilling_card_hash_object
     * @return void
     */
    public function it_should_contain_the_correct_value(NetbillingCardHash $cardHash): void
    {
        $this->assertSame((string) $cardHash, $this->validDecodedCardHashString);
    }

    /**
     * @test
     * @dataProvider invalidCardHashProvider
     * @param string $invalidValue Invalid values
     * @return void
     * @throws \Exception
     */
    public function it_should_throw_exception_for_invalid_strings(string $invalidValue): void
    {
        $this->expectException(InvalidCreditCardInformationException::class);
        NetbillingCardHash::create($invalidValue);
    }

    /**
     * @return array
     */
    public function invalidCardHashProvider()
    {
        return [
            '5 chars in "last 4 digits of card"' => ['Q1M6MTIzNDI0NTI6MTIzNDU='], // 'CS:12342452:12345'
            '1 char in "last 4 digits of card"'  => ['Q1M6MTIzNDI0NTI6MQ=='], //'CS:12342452:1'
            '3 chars in "last 4 digits of card"' => ['Q1M6MTIzNDI0NTI6MTM1'], //'CS:12342452:135'
            'not beginning with CS'              => ['QUI6MTIzNDUyMzM6MTIzNA=='], //'AB:12345233:1234'
            '13 chars in transaction id'         => ['Q1M6MTExMTExMTExMTExMToxMjM0'], //'CS:1111111111111:1234'
            'no separators'                      => ['Q1MxMjM0MTIzNDEyMzQ6MTIzNA=='], //'CS123412341234:1234'
        ];
    }
}
