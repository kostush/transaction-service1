<?php

declare(strict_types=1);

namespace App\Providers;

use Grpc\ChannelCredentials;
use Illuminate\Support\ServiceProvider;
use Probiller\Service\Config\ProbillerConfigClient;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponseExtraDataRepository;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigServiceClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\ConfigTranslatingService;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            ConfigServiceClient::class,
            function () {
                $credentials = env('CONFIG_SERVICE_USE_SSL', true)
                    ? ChannelCredentials::createSsl() : ChannelCredentials::createInsecure();

                return new ConfigServiceClient(
                    new ProbillerConfigClient(
                        env('CONFIG_SERVICE_HOST'),
                        ['credentials' => $credentials]
                    )
                );
            }
        );

        $this->app->bind(
            DeclinedBillerResponseExtraDataRepository::class,
            ConfigTranslatingService::class
        );
    }
}
