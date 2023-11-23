<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use Tests\UnitTestCase;

class QyssoBillerSettingsTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     */
    public function to_array_method_should_contain_all_needed_keys(): void
    {
        $okFlag = true;

        $neededKeys = [
            'companyNum',
            'personalHashKey',
            'redirectUrl',
            'notificationUrl'
        ];

        $object = QyssoBillerSettings::create(
            'companyNum',
            'personalHashKey',
            'redirectUrl',
            'notificationUrl'
        );

        $toArrayData = $object->toArray();

        foreach ($neededKeys as $key) {
            if (!isset($toArrayData[$key])) {
                $okFlag = false;
                break;
            }
        }

        $this->assertTrue($okFlag);
    }
}
