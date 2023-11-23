<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

interface PaymentInformationObfuscated extends ObfuscatedData
{
    /**
     * @return mixed
     */
    public function returnObfuscatedDataForPersistence();
}
