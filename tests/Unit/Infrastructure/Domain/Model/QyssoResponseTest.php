<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Model;

use ProBillerNG\Transaction\Domain\Services\QyssoNewSaleAdapter as QyssoNewSaleAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoPostbackBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\QyssoReturnBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\QyssoTranslatingService;
use Tests\UnitTestCase;

class QyssoResponseTest extends UnitTestCase
{
    public function test_approved_transaction_postback_received()
    {
        $file   = __DIR__ . '/qysso_success_received_from_postback.json';
        $result = QyssoPostbackBillerResponse::create(file_get_contents($file));
        $this->assertTrue($result->approved());
        $this->assertEquals('000', $result->code());
        $this->assertEquals('2-1', $result->billerTransactionId());
    }

    public function test_declined_transaction_postback_received()
    {
        $file   = __DIR__ . '/qysso_declined_received_from_postback.json';
        $result = QyssoPostbackBillerResponse::create(file_get_contents($file));
        $this->assertTrue($result->declined());
        $this->assertEquals('002', $result->code());
        $this->assertEquals('3-2',$result->billerTransactionId());
    }

    public function test_approved_transaction_return_received()
    {
        $file = __DIR__ . '/qysso_success_received_from_return.txt';
        parse_str(file_get_contents($file), $parsed);
        $result = QyssoReturnBillerResponse::create(json_encode($parsed));
        $this->assertTrue($result->approved());
        $this->assertEquals('000', $result->code());
        $this->assertEquals('2-1',$result->billerTransactionId());
    }

    public function test_declined_transaction_return_received()
    {
        $file = __DIR__ . '/qysso_declined_received_from_return.txt';
        parse_str(file_get_contents($file), $parsed);
        $result = QyssoReturnBillerResponse::create(json_encode($parsed));
        $this->assertTrue($result->declined());
        $this->assertEquals('002', $result->code());
        $this->assertEquals('3-2',$result->billerTransactionId());
    }

    public function test_approved_transaction_return_received_using_translating_service()
    {
        $file = __DIR__ . '/qysso_success_received_from_return.txt';
        parse_str(file_get_contents($file), $parsed);

        $translatingService = new QyssoTranslatingService($this->createMock(QyssoNewSaleAdapterInterface::class));
        $result             = $translatingService->translatePostback(json_encode($parsed), $_ENV['QYSSO_PERSONAL_HASH_KEY_3']);
        $this->assertTrue($result->approved());
        $this->assertEquals('2-1',$result->billerTransactionId());
    }

    public function test_approved_transaction_postback_received_using_translating_service()
    {
        $file = __DIR__ . '/qysso_success_received_from_postback.json';

        $translatingService = new QyssoTranslatingService($this->createMock(QyssoNewSaleAdapterInterface::class));
        $result             = $translatingService->translatePostback(file_get_contents($file), $_ENV['QYSSO_PERSONAL_HASH_KEY_3']);
        $this->assertTrue($result->approved());
        $this->assertEquals('2-1',$result->billerTransactionId());
    }
}
