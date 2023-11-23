<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Repository;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\InMemoryRepository;
use Illuminate\Support\Facades\App;
use Redis;

class RedisRepository implements InMemoryRepository
{

    /**
     * @var Redis|null
     */
    private $redis;

    /**
     * RedisRepository constructor.
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct()
    {
        try {
            $this->redis = App::make(Redis::class);
        } catch (\Throwable $e) {
            Log::error(
                "CVVRedis Could not connect to redis. An error occurred. Proceeding without redis.",
                [
                    "errorMessage" => $e->getMessage(),
                ]
            );

            $this->redis = null;
        }
    }

    /**
     * @param string      $transactionId
     * @param string      $billerName
     * @param string|null $cvv
     *
     * @throws  \ProBillerNG\Logger\Exception
     */
    public function storeCvv(
        string $transactionId,
        string $billerName,
        ?string $cvv
    ): void {
        $key = $this->getKey($transactionId, $billerName);

        try {
            $ttl = ['nx', 'ex' => (int) env('REDIS_RECORD_TTL')];
            if (!$this->redis->set($key, (string) $cvv, $ttl)) {
                Log::error(
                    "CVVRedis Key was not set.",
                    [
                        "key"           => $key,
                        "redisOn"       => $this->isConnected(),
                        "transactionId" => $transactionId,
                    ]
                );

                return;
            }

            Log::info(
                "CVVRedis Key was stored",
                [
                    "key"           => $key,
                    "transactionId" => $transactionId,
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                "CVVRedis Could not store CVV because of an error.",
                [
                    "exceptionMessage" => $e->getMessage(),
                    "key"              => $key,
                    "redisOn"          => $this->isConnected(),
                    "transactionId"    => $transactionId,
                ]
            );
        }
    }

    /**
     * @param string $transactionId
     * @param string $billerName
     *
     * @return  string|null
     * @throws  \ProBillerNG\Logger\Exception
     */
    public function retrieveCvv(
        string $transactionId,
        string $billerName
    ): ?string {
        $key = $this->getKey($transactionId, $billerName);

        try {
            $cvv = $this->redis->get($key);
            if (empty($cvv)) {
                Log::error(
                    "CVVRedis Could not retrieve CVV from Redis for specified key.",
                    [
                        "key"           => $key,
                        "redisOn"       => $this->isConnected(),
                        "transactionId" => $transactionId,
                    ]
                );
            } else {
                Log::info(
                    "CVVRedis Retrieved CVV from Redis",
                    [
                        "key"           => $key,
                        "transactionId" => $transactionId,
                    ]
                );
            }

            return $cvv ?: null;
        } catch (\Throwable $e) {
            Log::error(
                "CVVRedis Could not retrieve CVV from Redis because of an error.",
                [
                    "exceptionMessage" => $e->getMessage(),
                    "key"              => $key,
                    "redisOn"          => $this->isConnected(),
                    "transactionId"    => $transactionId,
                ]
            );

            return null;
        }
    }

    /**
     * @param string $transactionId
     * @param string $billerName
     *
     * @throws \ProBillerNG\Logger\Exception
     */
    public function deleteCvv(string $transactionId, string $billerName): void
    {
        $key = $this->getKey($transactionId, $billerName);

        try {
            if (!$this->redis->del($key)) {
                Log::error(
                    "CVVRedis Could not delete CVV from Redis for specified key.",
                    [
                        "key"           => $key,
                        "redisOn"       => $this->isConnected(),
                        "transactionId" => $transactionId,
                    ]
                );
            } else {
                Log::info(
                    "CVVRedis Deleted CVV from Redis",
                    [
                        "key"           => $key,
                        "transactionId" => $transactionId,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error(
                "CVVRedis Could not delete CVV from Redis for specified key because of an error.",
                [
                    "errorMessage"  => $e->getMessage(),
                    "key"           => $key,
                    "redisOn"       => $this->isConnected(),
                    "transactionId" => $transactionId,
                ]
            );
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->redis instanceof Redis) {
            return $this->redis->isConnected();
        }

        return false;
    }

    /**
     * @param string $transactionId
     * @param string $billerName
     *
     * @return string
     */
    public function getKey(string $transactionId, string $billerName): string
    {
        return "transaction:" . $transactionId . ":" . $billerName;
    }
}