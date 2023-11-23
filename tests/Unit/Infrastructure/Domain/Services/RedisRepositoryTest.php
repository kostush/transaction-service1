<?php


namespace Infrastructure\Domain\Services;


use ProBillerNG\Transaction\Infrastructure\Domain\Repository\RedisRepository;
use Tests\UnitTestCase;

class RedisRepositoryTest extends UnitTestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function it_should_connect_to_redis()
    {
        //Connect and authenticate into the Redis Server
        $redis = new RedisRepository();

        $this->assertSame(true, $redis->isConnected());

        return $redis;
    }

    /**
     * @test
     * @depends it_should_connect_to_redis
     * @param $redis
     */
    public function it_should_set_and_get_the_cvv_correctly_and_without_errors($redis)
    {
        $transactionId = $this->faker->uuid;
        $billerName    = "rocketgate";
        $cvv           = "123";

        $redis->storeCvv($transactionId, $billerName, $cvv);

        $this->assertSame($cvv, $redis->retrieveCvv($transactionId, $billerName));

    }

    /**
     * @test
     * @depends it_should_connect_to_redis
     * @param $redis
     */
    public function it_should_delete_the_cvv_without_errors($redis)
    {
        $transactionId = $this->faker->uuid;
        $billerName    = "rocketgate";
        $cvv           = "123";

        $redis->storeCvv($transactionId, $billerName, $cvv);
        $redis->deleteCvv($transactionId, $billerName);

        $this->assertNull($redis->retrieveCvv($transactionId, $billerName));

    }
}